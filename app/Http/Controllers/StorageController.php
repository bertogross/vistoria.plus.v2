<?php

namespace App\Http\Controllers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function index()
    {
        $currentUserId = auth()->id();

        $connectionId = getCurrentConnectionByUserId($currentUserId);

        $directory = 'vpApp'.$connectionId.'/attachments'; // Base directory
        $files = collect(Storage::disk('public')->allFiles($directory))
            ->map(function ($file) {
                // Extracting date and filename
                $pathInfo = explode('/', $file);
                $year = $pathInfo[2] ?? null;
                $month = $pathInfo[3] ?? null;
                $filename = end($pathInfo);

                return [
                    'name' => $filename,
                    'date' => $year && $month ? "{$year}-{$month}" : '-',
                    'path' => $file,
                    'url' => asset('storage/' . $file),
                    'size' => Storage::disk('public')->size($file),
                    'lastModified' => Storage::disk('public')->lastModified($file),
                ];
            })
            ->sortByDesc('lastModified');

        // The total count of files
        $totalFiles = $files->count();

        // Calculate total usage in bytes
        $totalUsageBytes = $files->sum('size');

        // Convert total usage to gigabytes (GB)
        $totalUsageGB = number_format($totalUsageBytes / 1024 / 1024 / 1024, 2);

        $getUserData = getUserData($connectionId);

        $diskQuota = $getUserData->disk_quota; // Total available storage in GB
        $percentageUsed = ($totalUsageGB / $diskQuota) * 100;

        // Pagination
        $page = request('page', 1); // Get the current page or default to 1
        $perPage = 10; // Number of items per page
        $offset = ($page - 1) * $perPage; // Calculate the offset
        $items = $files->slice($offset, $perPage); // Get the items for the current page
        $paginator = new LengthAwarePaginator($items, $files->count(), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return view('settings.storage', [
            'data' => $paginator,
            'totalFiles' => $totalFiles,
            'diskQuota' => $diskQuota,
            'totalUsage' => $totalUsageGB,
            'percentageUsed' => $percentageUsed
        ]);
    }
}
