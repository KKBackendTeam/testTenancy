<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\ApplicantRequirement;
use App\Models\Chasing;
use App\Models\EmailTemplate;
use App\Models\EmploymentRequirement;
use App\Models\FinancialConfiguration;
use App\Models\GuarantorRequirement;
use App\Models\LandlordRequirement;
use App\Models\MailServer;
use App\Models\TenancyRequirement;
use App\Models\DefaultEmailTemplet;
use App\Models\DefaultTextForSpecificArea;
use App\Models\TextForSpecificArea;
use Illuminate\Http\Request;
use App\Traits\AllPermissions, App\Traits\LastStaffActionTrait;
use App\Http\Requests\DefaultSetting\ResetMailTemplateRequest;
use App\Http\Requests\DefaultSetting\ResetTextTemplateRequest;
use App\Models\QuarterlyRequirement;

class DefaultSettingController extends Controller
{
    use AllPermissions, LastStaffActionTrait;

     /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function defaultSettings(Request $request)
    {
        $mailData = DefaultEmailTemplet::where('mail_code', '!=', 'ARE')->get();
        $texForSpecificArea = DefaultTextForSpecificArea::all();

        $id = isset($request['setDefaultSetting']) ? $request['agency_id'] : authAgencyId();

        $agency = Agency::where('id', $id)->firstOrFail();

        if ($agency->isDefaultSetting == 1) {
            return response()->json(['saved' => false, 'reason' => 'Already default setting updated']);
        };

        Chasing::updateOrCreate(['agency_id' => $id], ['stalling_time' => 1, 'response_time' => 1]);
        ApplicantRequirement::updateOrCreate(['agency_id' => $id]);
        EmploymentRequirement::updateOrCreate(['agency_id' => $id]);
        FinancialConfiguration::updateOrCreate(['agency_id' => $id], ['amount' => 1]);
        GuarantorRequirement::updateOrCreate(['agency_id' => $id]);
        LandlordRequirement::updateOrCreate(['agency_id' => $id]);
        QuarterlyRequirement::updateOrCreate(['agency_id' => $id]);
        TenancyRequirement::updateOrCreate(['agency_id' => $id]);

        foreach ($mailData as $key => $singleData) {

            $mm = new EmailTemplate();
            $mm['agency_id'] = $id;
            $mm['mail_code'] = $singleData->mail_code;
            $mm['data'] = $singleData->data;
            $mm->save();
        }

        foreach ($texForSpecificArea as $key => $singleText) {

            $text = new TextForSpecificArea();
            $text['agency_id'] = $id;
            $text['text_code'] = $singleText->text_code;
            $text['data'] = $singleText->data;
            $text['type'] = $singleText->type ?? '';
            $text['name'] = $singleText->name ?? '';
            $text->save();
        }

        $agency->update(['isDefaultSetting' => 1]);

        return response()->json(['saved' => true, 'reason' => 'Update done']);
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\ResetMailTemplateRequest  $request
     * @param  \App\DefaultEmailTemplet  $defaultEmailTemplet
     * @return \Illuminate\Http\Response
     */
    public function resetMailTemplate(ResetMailTemplateRequest $request)
    {
        $templateData = DefaultEmailTemplet::where('mail_code', $request['mail_code'])->first();
        EmailTemplate::where('agency_id', authAgencyId())->where('mail_code', $request['mail_code'])
            ->update(['data' => $templateData->data]);

        $this->lastStaffAction('Reset email template');
        return response()->json(['saved' => true, 'reason' => 'Mail template reset successfully!']);
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\ResetTextTemplateRequest  $request
     * @param  \App\DefaultTextForSpecificArea  $defaultTextForSpecificArea
     * @return \Illuminate\Http\Response
     */
    public function resetTextTemplate(ResetTextTemplateRequest $request)
    {
        $defaultText = DefaultTextForSpecificArea::where('text_code', $request['text_code'])->first();
        TextForSpecificArea::where('agency_id', authAgencyId())->where('text_code', $request['text_code'])
            ->update(['data' => $defaultText->data]);

        $this->lastStaffAction('Reset text for specific area');
        return response()->json(['saved' => true, 'reason' => 'Text for specific area reset successfully']);
    }
}
