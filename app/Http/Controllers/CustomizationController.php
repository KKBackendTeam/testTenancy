<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Http\Requests\Customization\EmailTemplateRequest;
use App\Http\Requests\TextForSpecificAreaRequest;
use App\Models\TextForSpecificArea;
use App\Traits\AllPermissions, App\Traits\LastStaffActionTrait;

class CustomizationController extends Controller
{
    use AllPermissions, LastStaffActionTrait;

    /**
     * Get the email template.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmailTemplate()
    {
        $emailTemplates = EmailTemplate::where('agency_id', authAgencyId())->latest()->get();
        return response()->json(['saved' => true, 'agency' => agencyData(), 'template' => $emailTemplates]);
    }

    /**
     * Store or update the email template.
     *
     * @param  \Illuminate\Http\EmailTemplateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postEmailTemplate(EmailTemplateRequest $request)
    {
        if ($mm = EmailTemplate::where('agency_id', authAgencyId())->where('mail_code', $request['mail_code'])->first()) {
        } else {
            $mm = new EmailTemplate();
        }
        $mm['agency_id'] = authAgencyId();
        $mm['mail_code'] = $request['mail_code'];
        $mm['data'] = json_encode($request['mail_data']);
        $mm->save();
        $this->lastStaffAction('Edit email template');
        return response()->json(['saved' => true, 'data' => $mm]);
    }

    /**
     * Get the text for specific area.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTextForSpecificArea()
    {
        $textForSpecificArea = TextForSpecificArea::where('agency_id', authAgencyId())->latest()->get();
        return response()->json(['saved' => true, 'text_area' => $textForSpecificArea]);
    }

    /**
     * Store or update the text for specific area.
     *
     * @param  \Illuminate\Http\TextForSpecificAreaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function postTextForSpecificArea(TextForSpecificAreaRequest $request)
    {
        if ($text = TextForSpecificArea::where('agency_id', authAgencyId())->where('text_code', $request['text_code'])->first()) {
        } else {
            $text = new TextForSpecificArea();
        }
        $name = $request['name'];
        $textCode = empty($request['text_code']) ? $this->generateCode($name) : $request['text_code'];
        $text['agency_id'] = authAgencyId();
        $text['text_code'] = $textCode;
        $text['data'] = json_encode($request['text_data']);
        $text['type'] = $request['type'] ?? '';
        $text['name'] = $request['name'] ?? '';
        $text->save();
        $this->lastStaffAction('Edit text-for specific area');
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
        $existingCodes = TextForSpecificArea::where('agency_id', authAgencyId())->pluck('text_code')->toArray();
        $uniqueCode = $code;
        $index = 1;
        while (in_array($uniqueCode, $existingCodes)) {
            $uniqueCode = $code . $index;
            $index++;
        }

        return $uniqueCode;
    }
}
