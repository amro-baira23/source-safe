<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\Group;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Throwable;

class SendNotificationToUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $fcm_tokens;
    /**
     * Create a new job instance.
     */
    public function __construct(private Group $group, private User $user, private $operation, private string $file)
    {
        $users= $group->users()->whereNot("user_id",$user->id)->get();
        $this->fcm_tokens = $users->pluck("fcm_token")->toArray();
    }
    /**
     * Execute the job.
     */
    public function handle(Messaging $messaging,): void
    {
        $message = CloudMessage::fromArray([
            'notification' => [     
                "title" => "File State Altered in Group {$this->group->name}",
                "body" => "User {$this->user->username} did {$this->operation} to File {$this->file}."
            ], 
        ]);
        try{
            Notification::create([
                "group_id" => $this->group->id,
                "title" => "File State Altered in Group {$this->group->name}",
                "body" => "User {$this->user->username} did {$this->operation} to File {$this->file}."
            ]);
            $messaging->sendMulticast($message,$this->fcm_tokens);
        } catch (Throwable $th){

        }
    }
}
