<?php
namespace App\Http\Controllers\Apps;
use App\Http\Controllers\Controller;
use App\Models\{DmsFolder, DmsDocument, Department};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DmsController extends Controller {
    public function index(Request $request) {
        $folderId = $request->folder_id;
        $folders = DmsFolder::where('parent_id', $folderId)->with('department')->get();
        $documents = DmsDocument::where('dms_folder_id', $folderId)->with('uploader')->get();
        $currentFolder = $folderId ? DmsFolder::find($folderId) : null;
        
        return Inertia::render('Apps/DMS/Index', [
            'folders' => $folders,
            'documents' => $documents,
            'currentFolder' => $currentFolder,
            'departments' => Department::all(['id','name'])
        ]);
    }
    public function storeFolder(Request $request) {
        $v = $request->validate(['name'=>'required', 'parent_id'=>'nullable|exists:dms_folders,id', 'department_id'=>'nullable|exists:departments,id']);
        DmsFolder::create($v);
        return back()->with('success', 'Folder created');
    }
    public function storeDocument(Request $request) {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,jpg,jpeg,png,gif',
            'title' => 'required',
            'dms_folder_id' => 'nullable|exists:dms_folders,id',
        ]);
        $file = $request->file('file');
        $path = $file->store('dms', 'public');
        
        DmsDocument::create([
            'title' => $request->title,
            'dms_folder_id' => $request->dms_folder_id,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
            'description' => $request->description
        ]);
        return back()->with('success', 'Document uploaded');
    }
    public function download(DmsDocument $document) {
        return Storage::disk('public')->download($document->file_path, $document->title . '.' . $document->file_type);
    }
    public function destroyDocument(DmsDocument $document) {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        return back();
    }
}