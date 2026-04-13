<?php

declare(strict_types=1);

namespace App\Ai\Actions;

use Pixelworxio\LaravelAiAction\DTOs\AgentContext;
use Pixelworxio\LaravelAiAction\DTOs\AgentResult;
use Pixelworxio\LaravelAiAction\Actions\RunAgentAction;
use Pixelworxio\LaravelAiAction\Concerns\InteractsWithAgent;
use Pixelworxio\LaravelAiAction\Contracts\AgentAction;

final class SummarizeSimpleEntry implements AgentAction
{
    use InteractsWithAgent;

    /**
     * Return the system-level instructions for this agent.
     */
    public function instructions(AgentContext $context): string
    {
        return 'You are a professional accountant assistant. Your task is to provide a concise, professional summary of a simple bookkeeping entry (e.g., a payment or receipt). Explain the flow of money between accounts. Use a professional tone and keep the summary brief (1-2 sentences). Respond in the same language as the notes provided (usually Arabic).';
    }

    /**
     * Return the user-facing prompt for this agent.
     */
    public function prompt(AgentContext $context): string
    {
        /** @var \App\Models\SimpleEntry $entry */
        $entry = $context->record;

        $fromAccount = $entry->fromAccount?->AccountName;
        $toAccount = $entry->toAccount?->AccountName;
        $currency = $entry->currency?->CurrencyName ?? $entry->currency?->EnglishCode ?? 'YER';

        return sprintf(
            "Please summarize this entry:\n\nDate: %s\nAmount: %s %s\nFrom Account: %s\nTo Account: %s\nNotes: %s",
            $entry->TheDate?->format('Y-m-d'),
            $entry->Amount,
            $currency,
            $fromAccount,
            $toAccount,
            $entry->Notes ?? 'No notes provided'
        );
    }

    /**
     * Execute the agent action and return a typed AgentResult.
     *
     * Delegate to RunAgentAction::execute() — do not put business logic here.
     * Pre-processing belongs in instructions() and prompt(); Post-processing
     * belongs in callers that consume the returned AgentResult.
     *
     * @param AgentContext $context The runtime context for this invocation.
     * @return AgentResult The typed result wrapping the AI response.
     */
    public function handle(AgentContext $context): AgentResult
    {
        return app(RunAgentAction::class)->execute($this, $context);
    }
}
