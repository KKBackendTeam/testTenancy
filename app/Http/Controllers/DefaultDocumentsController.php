<?php

namespace App\Http\Controllers;

use Storage;
use App\Models\DefaultDocuments;
use App\Http\Requests\DefaultDocument\DefaultDocumentsRequest;
use App\Traits\AllPermissions;
use App\Traits\WorkWithFile, App\Traits\LastStaffActionTrait;

class DefaultDocumentsController extends Controller
{
    use AllPermissions, WorkWithFile, LastStaffActionTrait;

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function postAddDefaultDocuments(DefaultDocumentsRequest $request)
    {
        $image_name = $this->file_upload($request['file'], "defaultDocuments", $request['file_name']);

        if ($image_name == 'virus_file') {
            return response()->json([
                'saved' => false,
                'statusCode' => 4578,
                'message' => 'The document is a virus file'
            ]);
        }

        $data = $request->all();
        $data['doc'] = $image_name;
        $data['agency_id'] = authAgencyId();
        DefaultDocuments::create($data);

        $this->lastStaffAction('Add new default document');
        return response()->json(['saved' => true]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\DefaultDocumentsRequest  $request
     * @param  \App\DefaultDocuments  $id
     * @return \Illuminate\Http\Response
     */
    public function postUpdateDefaultDocuments(DefaultDocumentsRequest $request)
    {
        $agencyId = authAgencyId();
        $documentData = DefaultDocuments::where('agency_id', $agencyId)->where('id', $request['id'])->firstOrFail();

        $data = $request->all();

        if (!Storage::exists('/public/agency/default_documents/' . $request['file'])) {
            $validator = validator($request->only('file'), [
                'file' => 'required|document_check'
            ]);

            if ($validator->fails()) return response()->json(['saved' => false, 'errors' => $validator->errors()]);

            $this->deleteFile('defaultDocuments', $documentData['doc']);
            $image_name = $this->file_upload($request['file'], "defaultDocuments", $request['file_name']);
            if ($image_name == 'virus_file') {
                return response()->json([
                    'saved' => false,
                    'statusCode' => 4578,
                    'message' => 'The document is a virus file'
                ]);
            }
            $data['doc'] = $image_name;
        }

        DefaultDocuments::updateOrCreate(['id' => $request['id'], 'agency_id' => $agencyId], $data);
        $this->lastStaffAction('Edit default document');
        return response()->json(['saved' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DefaultDocuments  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteDefaultDocuments($id)
    {
        $documentData = DefaultDocuments::where('agency_id', authAgencyId())->where('id', $id)->firstOrFail();
        $this->deleteFile('defaultDocuments', $documentData['doc']);
        $documentData->delete();

        $this->lastStaffAction('Delete default document');
        return response()->json(['saved' => true]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllTheDocuments()
    {
        if (agencyAdmin() || $this->accessCustomization()) {
            return response()->json(['saved' => true, 'documents' => DefaultDocuments::where('agency_id', authAgencyId())->latest()->get()]);
        }
        return response()->json(['saved' => false]);
    }
}
