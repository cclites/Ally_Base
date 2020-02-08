<?php

namespace App\Http\Controllers;

use App\Caregiver;
use App\Exports\GenericExport;
use App\Note;
use App\OfficeUser;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\CreateNoteRequest;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Response;


class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('notes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('notes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateNoteRequest $request)
    {
        if ($note = Note::create(array_merge($request->validated(), [
            'created_by' => auth()->id(),
        ]))) {

            if ($request->input('modal')) {
                return new CreatedResponse('Note Created', $note->load('creator', 'client', 'caregiver', 'prospect', 'referral_source'));
            }

            return new CreatedResponse('Note Created', [], '/notes');
        }
        
        return new ErrorResponse(500, 'The note could not be created.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function update(CreateNoteRequest $request, Note $note)
    {
        if ($note->update($request->validated())) {
            if ($request->input('modal')) {
                return new SuccessResponse('Note has been updated.', $note->fresh()->load('creator', 'client', 'caregiver', 'prospect', 'referral_source'));
            }

            return new SuccessResponse('Note has been updated');
        }

        return new ErrorResponse(500, 'The note could not be created.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Note  $note
     * @return \Illuminate\Http\Response
     */
    public function destroy(Note $note)
    {
        if ($note->delete()) {
            return new SuccessResponse('Note deleted.', $note->toArray());
        }

        return new ErrorResponse(500, 'The note could not be deleted.');
    }

    /**
     * Handle search filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $notes = Note::forRequestedBusinesses()
            ->with('caregiver', 'client', 'prospect', 'referral_source')
            ->when($request->filled('start_date'), function ($query) use ($request) {
                return $query->where('created_at', '>=', Carbon::parse($request->start_date)->subDay());
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                return $query->where('created_at', '<=', Carbon::parse($request->end_date)->addDay());
            })
            ->when($request->filled('caregiver'), function ($query) use ($request) {
                return $query->where('caregiver_id', $request->caregiver);
            })
            ->when($request->filled('client'), function ($query) use ($request) {
                return $query->where('client_id', $request->client);
            })
            ->when($request->filled('prospect'), function ($query) use ($request) {
                return $query->where('prospect_id', $request->prospect);
            })
            ->when($request->filled('referral_source'), function ($query) use ($request) {
                return $query->where('referral_source_id', $request->referral_source);
            })
            ->when($request->filled('user'), function ($query) use ($request) {
                return $query->where('created_by', $request->user);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->when($request->filled('free_form'), function ($query) use ($request) {
                return $query->where('body', 'like', '%' . $request->free_form . '%')
                       ->orWhere('tags', 'like', '%' . $request->free_form . '%');
            })
            ->when($request->filled('template'), function($query) use ($request){
                // return $query->whereHas('template', function($query) use ($request){
                //   return $query->where('name', 'like', '%'.$request->input('template').'%');
                // });
                return $query;
            })
            ->get();

        if($request->print){
            return $this->printReport($notes);
        }

        return response()->json($notes);
    }

    /**
     * @param $role
     * @param $id
     * @param $type
     * @return PDF or XLS
     */
    public function download($role, $id, $type)
    {
        if($role === 'caregiver'){
            $user = \App\Caregiver::find($id)->load('notes');
        }else if($role === 'client') {
            $user = \App\Client::find($id)->load('notes');
        }

        if($type === 'pdf'){
            $pdf = \PDF::loadView('business.notes', ['user'=>$user]);
            return $pdf->download( $user->name . '_notes.pdf' );
        }else if($type === 'excel'){
            return $this->generateXls($user);
        }
    }

    /**
     * Generate xls file
     *
     * @param $user
     * @return \Maatwebsite\Excel\BinaryFileResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function generateXls($user)
    {
        $xls = $user->notes->map(function($note){
            return [
                'Title' => $note->title,
                'Created By' => $note->creator->name,
                'Tags' => $note->tags,
                'Date' => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $note->created_at)->format('m/d/Y  h:i:s A'),
                'Note' => $note->body
            ];
        })->toArray();

        return Excel::download(new GenericExport($xls), $user->name . '_Notes.xlsx');
    }

     /** Get the PDF printed output of the report.
     *
     * @return \Illuminate\Http\Response
     */
    public function printReport($data) : \Illuminate\Http\Response
    {
        $html = response(view('business.reports.communication_notes',['data'=>$data]))->getContent();
        $snappy = \App::make('snappy.pdf');

        return Response(
            $snappy->getOutputFromHtml($html),
            200,

            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Notes.pdf"'
            )
        );
    }

    /**
     * Get all note creators for the current chain.
     *
     * @param Request $request
     * @return Note[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function creators(Request $request)
    {
        $creators = Note::forRequestedBusinesses()
            ->with('creator')
            ->select('created_by')
            ->groupBy('created_by')
            ->get()
            ->map(function ($note) {
                return [
                    'id' => $note->creator->id,
                    'name' => $note->creator->nameLastFirst
                ];
            });

        return $creators;
    }
}
