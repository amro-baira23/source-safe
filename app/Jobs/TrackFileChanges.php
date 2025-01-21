<?php

namespace App\Jobs;

use App\Models\Lock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

class TrackFileChanges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private Lock $version;

    /**
     * Create a new job instance.
     */
    public function __construct(Lock $version)
    {
        $this->version = $version;
    
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $previous_version = Lock::where("created_at","<",$this->version->created_at)
            ->orderBy("created_at","desc")->first();
        $differ = new Differ(new UnifiedDiffOutputBuilder());
        $change = $differ->diff($previous_version->getFileContent(),$this->version->getFileContent());
        $this->version->update(["change" => $change]);
    }
}



