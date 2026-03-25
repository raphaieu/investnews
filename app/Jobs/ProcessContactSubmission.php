<?php

namespace App\Jobs;

use App\Services\Contacts\ContactService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessContactSubmission implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    /**
     * @param  array{name: string, email: string, message: string}  $payload
     */
    public function __construct(
        public readonly array $payload
    ) {
        $this->onQueue('contacts');
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(ContactService $contactService): void
    {
        $contactService->store($this->payload);
    }
}
