<?php

namespace App\Http\Controllers;

use App\Models\Chasing;
use App\Models\FinancialConfiguration;
use App\Http\Requests\Customization\ChasingSettingRequest;
use App\Http\Requests\Customization\FinancialConfiguarationRequest;
use App\Http\Requests\MailServerRequest;
use App\Models\Landloard;
use App\Mail\CreditEmail;
use App\Models\MailServer;
use App\Notifications\Agency\CreditNotification;
use App\Models\Property;
use Exception;
use Mail;
use Storage;
use Illuminate\Support\Str;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_TransportException;
use Illuminate\Http\Request;
use App\Traits\AllPermissions;
use App\Traits\WorkWithFile;
use App\Traits\ConfigrationTrait;
use App\Traits\LastStaffActionTrait;
use App\Traits\TextForSpecificAreaTrait;
use App\Http\Requests\Agency\AgencyProfileRequest;
use App\Models\Agency;
use App\Http\Requests\Configuration\BuyCreditRequest;

class ConfigurationController extends Controller
{
    use AllPermissions, WorkWithFile, ConfigrationTrait, LastStaffActionTrait, TextForSpecificAreaTrait;

    /**
     * Save or update mail server settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function saveMailServer($request)
    {
        $mailData = $request->all();
        $authAgencyId = authAgencyId();
        $mailData['agency_id'] = $authAgencyId;
        return MailServer::updateOrCreate(['agency_id' => $authAgencyId], $mailData);
    }

    /**
     * Retrieve mail server settings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMailServer()
    {
        return response()->json(['saved' => true, 'mail_server' => MailServer::where('agency_id', authAgencyId())
            ->firstOrFail(['driver', 'host', 'port', 'from_name', 'from_address', 'encryption', 'username', 'password'])]);
    }

    /**
     * Update mail server settings and handle exceptions.
     *
     * @param  \App\Http\Requests\MailServerRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postMailServer(MailServerRequest $request)
    {
        try {
            $transport = new Swift_SmtpTransport(
                $request['host'],
                $request['port'],
                strtolower($request['encryption'])
            );
            $transport->setUsername($request['username']);
            $transport->setPassword($request['password']);

            $mailer = new Swift_Mailer($transport);

            $mailer->getTransport()->start();

            $mail_server = $this->saveMailServer($request);
            $this->lastStaffAction('Edit mail server setting');
            return response()->json([
                'saved' => true,
                'mailServer' => $mail_server->only('port', 'host', 'driver', 'encryption', 'from_address', 'from_name', 'password', 'username')
            ]);
        } catch (Swift_TransportException $e) {
            return response()->json(['saved' => false, 'statusCode' => 785, 'exception' => $e->getMessage()]); //mail server configuration credentials are not valid.
        } catch (Exception $e) {
            return response()->json(['saved' => false, 'statusCode' => 785, 'exception' => $e->getMessage()]); //mail server configuration credentials are not valid.
        }
    }

    /**
     * Update mail server settings and handle exceptions.
     *
     * @param  \App\Http\Requests\MailServerRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChasingSetting()
    {
        $authAgencyId = authAgencyId();
        return response()->json(['saved' => true, 'chasingSetting' => $this->chasingSetting($authAgencyId)]);
    }

    /**
     * Retrieve financial configuration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFinancialConfiguration()
    {
        return response()->json(['saved' => true, 'financialConfiguration' => $this->financialConfiguration()]);
    }

    /**
     * Upload media logo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postMediaLogo(Request $request)
    {
        $validator = validator($request['mediaLogo'], [
            'media_logo' => 'required|file_type_check',
        ]);

        if ($validator->fails()) return response()->json(['saved' => false, 'errors' => $validator->errors()]);

        $agency_info = agencyData();

        if (!empty($request['mediaLogo']['media_logo'])) {
            $image_name = $this->file_upload($request['mediaLogo']['media_logo'], "media_logo", null);

            if ($image_name == 'virus_file') {
                return response()->json([
                    'saved' => false,
                    'statusCode' => 4578,
                    'message' => 'The media logo is a virus file'
                ]);
            } else {
                $this->deleteFile('media_logo', $agency_info->media_logo);
                $agency_info->media_logo = $image_name;
            }
        }
        $agency_info->save();
        $this->lastStaffAction('Edit media logo');
        return response()->json(['saved' => true]);
    }

    /**
     * Update chasing setting.
     *
     * @param  \App\Http\Requests\ChasingSettingRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postChasingSetting(ChasingSettingRequest $request)
    {
        $authAgencyId = authAgencyId();
        $c = Chasing::updateOrCreate(['agency_id' => $authAgencyId], addAgencyIdInRequest($request, $authAgencyId));
        $this->lastStaffAction('Edit chasing setting');
        return response()->json(['saved' => true, 'chasingSetting' => $c]);
    }

    /**
     * Update financial configuration.
     *
     * @param  \App\Http\Requests\FinancialConfiguarationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postFinancialConfiguration(FinancialConfiguarationRequest $request)
    {
        $authAgencyId = authAgencyId();
        $fc = FinancialConfiguration::updateOrCreate(['agency_id' => $authAgencyId], addAgencyIdInRequest($request, $authAgencyId));
        $this->lastStaffAction('Edit financial configuration setting');
        return response()->json(['saved' => true, 'financialConfiguration' => $fc]);
    }

    /**
     * Download property CSV demo.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPropertyCsvDemo()
    {
        return response()->download(storage_path('app/public/csv/property/property_demo.csv'));
    }

    /**
     * Download landlord CSV demo.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLandlordCsvDemo()
    {
        return response()->download(storage_path('app/public/csv/landlord/landlord_demo.csv'));
    }

    /**
     * Process and save bulk landlord data from CSV file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postBulkLandlord(Request $request)
    {
        try {
            $contents = file_get_contents($request['bulk_l']['bulk_landlord']);
            $unique_name = md5(Str::random(20)) . time() . '.csv';

            Storage::put('public/csv/landlord/' . $unique_name, $contents);

            $total = $this->importCsv($unique_name, $type = 'landlord');

            return response()
                ->json([
                    'saved' => true,
                    'total' => 'Bulk Landlord Data ' . $total[2] . '/' . $total[1] . ' inserted Successfully!.'
                ]);
        } catch (\Exception $e) {
            return response()->json(['saved' => false, 'statusCode' => 400, 'error' => 'Please provide a CSV file with the same columns as those in the demo file. ']);
        }
    }

    /**
     * Validate landlord data.
     *
     * @param  array  $checkLandlord
     * @return bool
     */
    public function validateLandlord($checkLandlord)
    {
        $validate = validator($checkLandlord, [
            'First name' => 'required',
            'Last name' => 'required',
            'Company name' => 'required',
            'Postcode' => 'required',
            'Street' => 'required',
            'Town' => 'required',
            'Country' => 'required',
            'Mobile' => 'required',
            'Email' => 'required|unique:landloards,email',
        ]);

        if ($validate->fails()) {
            return false;
        }
        return true;
    }

    /**
     * Process and save bulk property data from CSV file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postBulkProperty(Request $request)
    {
        try {
            $contents = file_get_contents($request['bulk_p']['bulk_property']);
            $unique_name = md5(Str::random(20)) . time() . '.csv';

            Storage::put('public/csv/property/' . $unique_name, $contents);

            $total = $this->importCsv($unique_name, $type = 'property');

            return response()
                ->json([
                    'saved' => true, 'total' => 'Bulk Property Data ' . $total[2] . '/' . $total[1] . ' inserted Successfully!.'
                ]);
        } catch (\Exception $e) {
            return response()->json(['saved' => false, 'statusCode' => 400, 'error' => 'Please provide a CSV file with the same columns as those in the demo file. ']);
        }
    }

    /**
     * Validate property data.
     *
     * @param  array  $validateProperty
     * @return bool
     */
    public function validateProperty($validateProperty)
    {
        $validate = validator($validateProperty, [
            'Landlord email' => 'required',
            'Property reference' => 'required|unique:properties,property_ref',
            'Postcode' => 'required',
            'Street' => 'required',
            'Town' => 'required',
            'Country' => 'required',
            'Parking' => ' required',
            'Bedroom' => 'required',
            'Rent include' => 'required',
            'Monthly amount' => 'required',
            'Deposite amount' => 'required',
            'Holding amount' => 'required'
        ]);

        if ($validate->fails()) {
            return false;
        }
        return true;
    }

    /**
     * Import data from CSV file and save to database.
     *
     * @param  string  $file_name
     * @param  string  $type
     * @return array
     */
    public function importCsv($file_name, $type)
    {
        try {
            $insertData = 0;
            if ($type == 'landlord') {
                $file = storage_path('app/public/csv/landlord/') . $file_name;
                $landlordArray = $this->csvToArray($file);
                $totalData = count($landlordArray);
                for ($i = 0; $i < $totalData; $i++) {
                    $true_false = $this->validateLandlord($landlordArray[$i]);
                    if ($true_false) {

                        $authUserData  = authUserData();
                        $csv_landlord = new Landloard();
                        $csv_landlord->agency_id = $authUserData->agency_id;
                        $csv_landlord->creator_id = $authUserData->id;
                        $csv_landlord->f_name = $landlordArray[$i]['First name'];
                        $csv_landlord->display_name = $landlordArray[$i]['Company name'];
                        $csv_landlord->l_name = $landlordArray[$i]['Last name'];
                        $csv_landlord->post_code = $landlordArray[$i]['Postcode'];
                        $csv_landlord->street = $landlordArray[$i]['Street'];
                        $csv_landlord->town = $landlordArray[$i]['Town'];
                        $csv_landlord->country = $landlordArray[$i]['Country'];
                        $csv_landlord->country_code = $landlordArray[$i]['Country code'];
                        $csv_landlord->mobile = $landlordArray[$i]['Mobile'];
                        $csv_landlord->email = $landlordArray[$i]['Email'];
                        $csv_landlord->save();
                        $insertData++;
                    }
                }
                return [true, $totalData, $insertData];
            } elseif ('property') {
                $file = storage_path('app/public/csv/property/') . $file_name;
                $propertyArray = $this->csvToArray($file);
                $totalData = count($propertyArray);
                for ($i = 0; $i < $totalData; $i++) {
                    $true_false = $this->validateProperty($propertyArray[$i]);
                    $l_email = preg_replace('/\s+/', '', $propertyArray[$i]['Landlord email']);
                    $landlord_id = Landloard::where('email', $l_email)->first();
                    if ($true_false && !empty($landlord_id)) {
                        $authUserData  = authUserData();
                        $csv_property = new Property();
                        $csv_property->agency_id = $authUserData->agency_id;
                        $csv_property->creator_id = authUserId();
                        $csv_property->landlord_id = $landlord_id->id;
                        $csv_property->property_ref =  $propertyArray[$i]['Property reference'];
                        $csv_property->post_code = $propertyArray[$i]['Postcode'];
                        $csv_property->street = $propertyArray[$i]['Street'];
                        $csv_property->town = $propertyArray[$i]['Town'];
                        $csv_property->country = $propertyArray[$i]['Country'];
                        $csv_property->status = 1;
                        $csv_property->parkingToggle = isset($this->parkingArray[strtolower($propertyArray[$i]['Parking'])]) ? $this->parkingArray[strtolower($propertyArray[$i]['Parking'])] : 1;
                        $csv_property->parking_cost = $parking_cost = is_null($propertyArray[$i]['Parking cost']) ? 0 : $propertyArray[$i]['Parking cost'];
                        $csv_property->parkingArray = isset($this->parkingStatusArray[strtolower($propertyArray[$i]['Parking status'])]) ? $this->parkingStatusArray[strtolower($propertyArray[$i]['Parking status'])] : 1;
                        $csv_property->bedroom = $propertyArray[$i]['Bedroom'];
                        $csv_property->restriction = $propertyArray[$i]['Restriction'];
                        $csv_property->rent_include = $propertyArray[$i]['Rent include'];
                        $csv_property->monthly_rent = $propertyArray[$i]['Monthly amount'];
                        $csv_property->total_rent = (int)($propertyArray[$i]['Monthly amount']) + (int)($parking_cost);
                        $csv_property->deposite_amount = $propertyArray[$i]['Deposite amount'];
                        $csv_property->holding_fee_amount = $propertyArray[$i]['Holding amount'];

                        $csv_property->save();
                        $insertData++;
                    }
                }
                return [true, $totalData, $insertData];
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Convert CSV file to associative array.
     *
     * @param  string  $filename
     * @param  string  $delimiter
     * @return array|false
     */
    public function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }
        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * Retrieve agency credits.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCredit()
    {
        return response()->json(['saved' => true, 'credits' => $this->agencyCredit()]);
    }

    /**
     * Purchase credits by agency.
     *
     * @param  \App\Http\Requests\BuyCreditRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postBuyCredit(BuyCreditRequest $request)
    {
        $agencyData = agencyData();
        $superAdmin = Agency::where('status', 2)->first();
        $data = $this->emailTemplateData('CRE', null, null, $agencyData, null, null, null, null, null, $superAdmin, $request);  //superAdmin()['agency_id']
        Mail::to(superAdmin()->email)->send(new CreditEmail($data, $agencyData));

        superAdmin()->notify(new CreditNotification($request, agencyData(), 0));

        return response()->json(['saved' => true]);
    }

    /**
     * Retrieve agency information including schedule time.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgencyInformation()
    {
        $agency = $this->agencyInformation();
        $agency->load('scheduleTime');
        return response()->json(['saved' => true, 'agency_information' => $agency]);
    }

    /**
     * Update agency profile information.
     *
     * @param  \App\Http\Requests\AgencyProfileRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdateAgencyProfile(AgencyProfileRequest $request)
    {
        $agency = Agency::where('id', authAgencyId())->firstOrFail();

        if ($agency->email != strtolower($request['email'])) {
            $validation = validator(
                $request->only('email'),
                [
                    'email' => 'required|email|unique:agencies'
                ]
            );

            if ($validation->fails()) return response()->json(['save' => false, 'errors' => $validation->errors()]);
        }
        $request['facebook'] =  $request['facebook'];
        $request['twitter'] = $request['twitter'];
        $request['google_plus'] = $request['google_plus'];

        $request['email'] = strtolower($request['email']);
        $agency->update($request->except(['total_credit', 'used_credit', 'schedule_time']));

        $timesData = $request['schedule_time'];

        foreach ($timesData as $time) {
            $agency->scheduleTime()->updateOrCreate(
                ['day' => $time['day']],
                [
                    'opening_time' => $time['opening_time'],
                    'closing_time' => $time['closing_time'],
                ]
            );
        }
        return response()->json(['saved' => true]);
    }
}
