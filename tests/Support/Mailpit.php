<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Reads the mail the application really sent over SMTP to the Mailpit
 * service (docker-compose.yml), through Mailpit's HTTP API.
 */
final class Mailpit
{
    /**
     * The first link in the latest message sent to $recipient whose URL
     * contains $contains. Waits for delivery.
     */
    public static function linkSentTo(string $recipient, string $contains): string
    {
        return retry(20, function () use ($recipient, $contains): string {
            $messages = Http::get(self::api('search'), ['query' => "to:\"{$recipient}\""])
                ->throw()
                ->json('messages', []);

            foreach ($messages as $message) {
                $text = Http::get(self::api('message/'.$message['ID']))->throw()->json('Text', '');

                if (preg_match('~https?://\S*'.preg_quote($contains, '~').'\S*~', (string) $text, $matches) === 1) {
                    return rtrim($matches[0], ')].,');
                }
            }

            throw new RuntimeException("No link containing [{$contains}] was mailed to [{$recipient}] yet.");
        }, 250);
    }

    /**
     * Deletes every message sent to $recipient, so test mail does not pile up.
     */
    public static function deleteMessagesTo(string $recipient): void
    {
        Http::delete(self::api('search'), ['query' => "to:\"{$recipient}\""]);
    }

    private static function api(string $path): string
    {
        return 'http://'.config('mail.mailers.smtp.host').':'.env('MAILPIT_API_PORT', 8025).'/api/v1/'.$path;
    }
}
