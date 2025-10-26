<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Project;

class ProjectStatusUpdated extends Notification
{
    use Queueable;

    protected $project;
    protected $status;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Project $project, $status)
    {
        $this->project = $project;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The status of your project has been updated.')
                    ->action('View Project', url('/projects/' . $this->project->slug))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'project_id' => $this->project->id,
            'project_title' => $this->project->title,
            'message' => 'Status proyek Anda "' . $this->project->title . '" telah diubah menjadi ' . $this->status,
        ];
    }
}
