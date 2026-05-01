<?php

namespace App\Observers;

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Services\Mailketing\MailketingSubscriberService;

class EpiChannelObserver
{
    public bool $afterCommit = true;

    public function created(EpiChannel $channel): void
    {
        if ($this->shouldSubscribe($channel, false)) {
            $this->subscribe($channel);
        }
    }

    public function updated(EpiChannel $channel): void
    {
        if (! $channel->wasChanged('status')) {
            return;
        }

        if ($this->shouldSubscribe($channel, $this->wasActiveBefore($channel))) {
            $this->subscribe($channel);
        }
    }

    private function subscribe(EpiChannel $channel): void
    {
        $channel->loadMissing('user');

        if ($channel->user) {
            app(MailketingSubscriberService::class)->addEpiChannelToEpiChannelList($channel->user);
        }
    }

    private function shouldSubscribe(EpiChannel $channel, bool $wasActiveBefore): bool
    {
        return $channel->status === EpiChannelStatus::Active && ! $wasActiveBefore;
    }

    private function wasActiveBefore(EpiChannel $channel): bool
    {
        $original = $channel->getOriginal('status');
        $value = $original instanceof EpiChannelStatus ? $original : EpiChannelStatus::tryFrom((string) $original);

        return $value === EpiChannelStatus::Active;
    }
}
