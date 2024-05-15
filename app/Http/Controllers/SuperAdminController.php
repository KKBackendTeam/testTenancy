<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\User;
use App\Models\Agency;
use App\Models\Tenancy;
use Illuminate\Http\Request;
use App\Traits\AllPermissions;
use App\Traits\WorkWithFile;
use App\Traits\RunTimeEmailConfigrationTrait;
use App\Traits\TextForSpecificAreaTrait;
use App\Http\Requests\Profile\SelfieRequest;
use App\Mail\AgencyEmail;
use App\Models\DefaultEmailTemplet;
use App\Models\DefaultTextForSpecificArea;
use App\Models\TextForSpecificArea;
use App\Http\Requests\Customization\EmailTemplateRequest;
use App\Http\Requests\TextForSpecificAreaRequest;
use App\Notifications\Agency\CreditNotification;
use App\Mail\CreditEmail;
use Illuminate\Support\Str;
use App\Traits\SortingActionTrait;
use App\Http\Requests\SuperAdmin\AddCreditRequest;

class SuperAdminController extends Controller
{
    use AllPermissions, WorkWithFile, RunTimeEmailConfigrationTrait, TextForSpecificAreaTrait;
    use SortingActionTrait;

    /**
     * Retrieve agencies with optional sorting.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgencies()
    {
        $agencies = Agency::where('status', '!=', 2)
            ->get()
            ->{isset($this->sortingAction[request('sort_action')]) ? $this->sortingAction[request('sort_action')] : $this->defaultSortingAction}(isset($this->sortingAgencyVariables[request('sort_by')]) ? $this->sortingAgencyVariables[request('sort_by')] : $this->defaultSortBy, $this->sortingString);

        $pageAndPagesize = $this->checkPageAndPagesize(request('page'), request('pagesize'));

        $data = $agencies->forPage($pageAndPagesize['page'], $pageAndPagesize['pageSize'])->values();

        return response()->json(['saved' => true, 'agencies' => ['data' => $data, 'total' => $agencies->count()]]);
    }

    /**
     * Retrieve tenancies along with related data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTenancies()
    {
        return response()->json([
            'creator' => User::get(['id', 'name', 'l_name']),
            'tenancies' => Tenancy::with('latest_update:tenancy_id,event_type')
                ->with('properties:id,post_code')
                ->with('landlords:id,f_name,l_name,street,town,country,post_code')
                ->with('users:id,name,l_name')->latest()->get()
        ]);
    }

    /**
     * Add a new agency.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postAddAgency(Request $request)
    {
        $validation = $this->agencyValidatorHelper($request);

        if ($validation->fails()) return response()->json(['saved' => false, 'errors' => $validation->errors()]);

        $im = !is_null($request['selfie_pic']) ? $this->file_upload($request['selfie_pic'], "selfie_pic", null) : "";
        if ($im == 'virus_file') {
            return response()->json([
                'saved' => false,
                'statusCode' => 4578,
                'message' => 'The agency profile is a virus file'
            ]);
        }

        $mediaLogo = !is_null($request['media_logo']) ? $this->file_upload($request['media_logo'], "media_logo", null) : "";
        if ($mediaLogo == 'virus_file') {
            return response()->json([
                'saved' => false,
                'statusCode' => 4578,
                'message' => 'The agency profile is a virus file'
            ]);
        }

        $agencyData = $request->toArray();
        $agencyData['email'] = strtolower($request['email']);
        $agencyData['media_logo'] = $mediaLogo;
        $agencyData['agency_address'] = strtolower(str_replace(' ', '', $agencyData['name'] . rand(100, 1000) . 'tenancy.com'));
        $agencyData['agency_confirm_link'] = config('global.frontSiteUrl') . ("/agency_link/" . Str::random(20));
        $agencyData['last_login'] = now()->toDateTimeString();
        $agencyData['total_credit'] = 10;
        $agencyObject = Agency::create($agencyData);

        $userData = $request->toArray();
        $userData['email'] = strtolower($request['email']);
        $userData['password'] = bcrypt($request['password']);
        $userData['agency_id'] = $agencyObject['id'];
        $userData['selfie_pic'] =  $im;
        $userData['roleStatus'] = $userData['email_status'] = 1;
        User::create($userData);

        $superAdmin = Agency::where('status', 2)->firstOrFail();

        $request['setDefaultSetting'] = 'Yes';
        $request['agency_id'] = $agencyObject['id'];
        app('App\Http\Controllers\DefaultSettingController')->defaultSettings($request);

        $this->runTimeEmailConfiguration($superAdmin->id);
        $data = $this->emailTemplateData('SA_ARE', null, null, $agencyObject, null, null, null, null, null, $superAdmin, null);
        Mail::to($request['email'])->send(new AgencyEmail($data, $superAdmin, $agencyObject));

        return response()->json(['saved' => true]);
    }

    /**
     * Edit an existing agency.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEditAgency(Request $request)
    {
        $validation = $this->agencyValidatorHelper($request);
        if ($validation->fails()) {
            return response()->json(['saved' => false, 'errors' => $validation->errors()]);
        }
        $agency = Agency::find($request['id']);
        if (!$agency) {
            return response()->json(['saved' => false]);
        }
        if (strtolower($agency->email) !== strtolower($request['email'])) {
            $emailValidation = validator($request->only('email'), [
                'email' => 'required|string|email|unique:agencies',
            ]);
            if ($emailValidation->fails()) {
                return response()->json(['saved' => false, 'errors' => $emailValidation->errors()]);
            }
            $agency->update(array_merge([
                'email' => strtolower($request['email']),
                'status' => 0,
                'agency_confirm_link' => config('global.frontSiteUrl') . "/agency_link/" . Str::random(20)
            ], $request->all()));
            $user = User::where('agency_id', $agency->id)->first();
            if ($user) {
                $user->update(['email' => strtolower($request['email'])]);
            }
            $this->updateSuperAdminAndSendEmail($agency);
        } else {
            $this->postEditAgencyHelper($request, $agency);
        }

        return response()->json(['saved' => true]);
    }
    private function updateSuperAdminAndSendEmail($agency)
    {
        $superAdmin = Agency::where('status', 2)->firstOrFail();
        $this->runTimeEmailConfiguration($superAdmin->id);
        $data = $this->emailTemplateData('SA_ARE', null, null, $agency, null, null, null, null, null, $superAdmin, null);
        Mail::to($agency->email)->send(new AgencyEmail($data, $superAdmin, $agency));
    }

    /**
     * Update the agency profile including selfie picture.
     *
     * @param SelfieRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdateAgencyProfile(SelfieRequest $request)
    {
        $user = User::where('agency_id', $request['id'])->where('roleStatus', 1)->firstOrFail();

        if (!empty($request['selfie_pic'])) {
            $image_name = $this->file_upload($request['selfie_pic'], "selfie_pic", null);
            if ($image_name == 'virus_file') {
                return response()->json([
                    'saved' => false,
                    'statusCode' => 4578,
                    'message' => 'The avatar is a virus file'
                ]);
            } else {
                $this->deleteFile('selfie_pic', $user->selfie_pic);
                $user->selfie_pic = $image_name;
            }
        }
        $user->save();

        return response()->json(['saved' => true, 'user_info' => $user]);
    }

    /**
     * Validate agency data based on the action (edit or new).
     *
     * @param mixed $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function agencyValidatorHelper($request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'action' => 'required'
        ];
        if ($request['action'] == "edit") $rules += ['id' => 'required|integer', 'email' => 'required|email'];
        if ($request['action'] == "new") $rules += ['email' => 'required|email|max:255|unique:users|unique:agencies', 'password' => 'required|min:6|confirmed'];
        if (!is_null($request['selfie_pic'])) $rules += ['selfie_pic' => 'required|only_image'];

        $validator = validator($request->all(), $rules);
        return $validator;
    }

    /**
     * Helper function to update agency information.
     *
     * @param mixed $request
     * @param \App\Models\Agency $agency
     * @return bool
     */
    public function postEditAgencyHelper($request, $agency)
    {
        $agency->update($request->all());
        return true;
    }


    /**
     * Get details of a specific agency by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgency($id)
    {
        return response()->json(['agency' => Agency::where('id', $id)->with(['agencyAdmin:id,agency_id,selfie_pic'])->firstOrFail()]);
    }

    /**
     * Delete an agency by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postDeleteAgency($id)
    {
        if ($agency = Agency::where('id', $id)->first()) {
            $agency->delete();
            return response()->json(['saved' => true]);
        }
        return response()->json(['saved' => false]);
    }

    /**
     * Add credit to an agency.
     *
     * @param AddCreditRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postAddCreditToAgency(AddCreditRequest $request)
    {
        $agencyData = Agency::where('id', $request['id'])->firstOrFail();
        $agencyData->increment('total_credit', $request['credit']);

        $superAdmin = authUserData();
        $data = $this->emailTemplateData('SA_CRE', null, null, $agencyData, null, null, null, null, null, $superAdmin, $request);
        Mail::to($agencyData->email)->send(new CreditEmail($data, $superAdmin));

        agencyAdminUserById($request['id'])->notify(new CreditNotification($request, $agencyData, 1));

        return response()->json(['saved' => true]);
    }

    /**
     * Get email templates.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmailTemplate()
    {
        return response()->json(['saved' => true, 'agency' => agencyData(), 'template' => DefaultEmailTemplet::all()]);
    }

    /**
     * Get text for specific areas.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTextForSpecificArea()
    {
        return response()->json(['saved' => true, 'text_area' => DefaultTextForSpecificArea::all()]);
    }

    /**
     * Update or create an email template.
     *
     * @param EmailTemplateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEmailTemplate(EmailTemplateRequest $request)
    {
        if ($mm = DefaultEmailTemplet::where('mail_code', $request['mail_code'])->first()) {
        } else {
            $mm = new DefaultEmailTemplet();
        }
        $mm['mail_code'] = $request['mail_code'];
        $mm['data'] = json_encode($request['mail_data']);
        $mm->save();
        return response()->json(['saved' => true, 'data' => $mm]);
    }

    /**
     * Update or create text for specific areas.
     *
     * @param TextForSpecificAreaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postTextForSpecificArea(TextForSpecificAreaRequest $request)
    {
        $authAgencyId = authAgencyId();
        if ($text = DefaultTextForSpecificArea::where('text_code', $request['text_code'])->first()) {
        } else {
            $text = new DefaultTextForSpecificArea();
        }
        $name = $request['name'];
        $textCode = empty($request['text_code']) ? $this->generateCode($name) : $request['text_code'];
        $text->name = $name;
        $text->type = $request['type'];
        $text->text_code = $textCode;
        $text->data = json_encode($request['text_data']);
        $text->save();

        $existingAgencies = TextForSpecificArea::select('agency_id')->distinct()->get();
        foreach ($existingAgencies as $agency) {
            $existingText = TextForSpecificArea::where('text_code', $textCode)
                ->where('agency_id', $agency->agency_id)
                ->exists();
            if (!$existingText && $agency->agency_id != $authAgencyId) {
                $newText = new TextForSpecificArea();
                $newText->agency_id = $agency->agency_id;
                $newText->name = $name;
                $newText->type = $request['type'];
                $newText->text_code = $textCode;
                $newText->data = json_encode($request['text_data']);
                $newText->save();
            }
        }

        return response()->json(['saved' => true, 'data' => $text]);
    }

    /**
     * Generate a unique code for a given name.
     *
     * @param string $name
     * @return string
     */
    public function generateCode($name)
    {
        $words = explode(' ', $name);
        $code = '';
        foreach ($words as $word) {
            $code .= strtoupper(substr($word, 0, 1));
        }
        $existingCodes = DefaultTextForSpecificArea::pluck('text_code')->toArray();
        $uniqueCode = $code;
        $index = 1;
        while (in_array($uniqueCode, $existingCodes)) {
            $uniqueCode = $code . $index;
            $index++;
        }

        return $uniqueCode;
    }
}
