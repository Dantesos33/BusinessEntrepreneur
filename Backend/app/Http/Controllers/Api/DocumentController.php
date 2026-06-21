<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::where('owner_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Document $d) => $d->toFrontendArray());

        return response()->json(['documents' => $documents]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB
            'shared' => 'sometimes|boolean',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/'.$request->user()->id, 'local');

        $document = Document::create([
            'owner_id' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'type' => $file->getClientOriginalExtension(),
            'size' => $this->formatBytes($file->getSize()),
            'shared' => $request->boolean('shared'),
            'path' => $path,
        ]);

        return response()->json(['document' => $document->toFrontendArray()], 201);
    }

    public function download(Request $request, int $id)
    {
        $document = Document::findOrFail($id);

        if ($document->owner_id !== $request->user()->id && ! $document->shared) {
            abort(403);
        }

        return Storage::disk('local')->download($document->path, $document->name);
    }

    public function destroy(Request $request, int $id)
    {
        $document = Document::where('owner_id', $request->user()->id)->findOrFail($id);

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return response()->json(['message' => 'Document deleted']);
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1).' '.$units[$i];
    }
}
