<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\QuotePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicQuoteController extends Controller
{
    public function show(string $quote, string $token): View
    {
        $quoteModel = $this->quoteForPublicAccess($quote, $token);

        $this->markViewed($quoteModel);
        $this->markExpiredIfNeeded($quoteModel);

        return view('client.quotes.show', [
            'quote' => $quoteModel,
        ]);
    }

    public function pdf(string $quote, string $token, QuotePdfService $quotePdf): Response
    {
        $quoteModel = $this->quoteForPublicAccess($quote, $token);

        $this->markViewed($quoteModel);
        $this->markExpiredIfNeeded($quoteModel);

        return $quotePdf->download($quoteModel);
    }

    public function respond(Request $request, string $quote, string $token): RedirectResponse
    {
        $quoteModel = $this->quoteForPublicAccess($quote, $token);
        $this->markViewed($quoteModel);
        $this->markExpiredIfNeeded($quoteModel);

        if (!$quoteModel->canCustomerRespond()) {
            return back()->with('error', 'Báo giá này đã hết hạn hoặc đã được phản hồi.');
        }

        $data = $request->validate(
            [
                'response' => ['required', Rule::in([Quote::STATUS_ACCEPTED, Quote::STATUS_REJECTED])],
                'customer_response_note' => ['nullable', 'string', 'max:1000'],
            ],
            [],
            [
                'response' => 'phản hồi',
                'customer_response_note' => 'ghi chú',
            ]
        );

        $quoteModel->forceFill([
            'status' => $data['response'],
            'customer_response_note' => trim((string) ($data['customer_response_note'] ?? '')) ?: null,
            'customer_responded_at' => now(),
        ])->save();

        return redirect()
            ->route('quotes.public.show', ['quote' => $quoteModel->quote_code, 'token' => $token])
            ->with('success', $quoteModel->status === Quote::STATUS_ACCEPTED
                ? 'Cảm ơn quý khách đã chấp nhận báo giá.'
                : 'Lux Auto đã ghi nhận phản hồi từ chối báo giá.');
    }

    private function quoteForPublicAccess(string $quoteCode, string $token): Quote
    {
        return Quote::query()
            ->with(['customer', 'car.modelInfo.brand', 'user', 'quotePromotions.promotion'])
            ->where('quote_code', $quoteCode)
            ->where('public_token', $token)
            ->firstOrFail();
    }

    private function markViewed(Quote $quote): void
    {
        if ($quote->viewed_at) {
            return;
        }

        $quote->forceFill(['viewed_at' => now()])->save();
    }

    private function markExpiredIfNeeded(Quote $quote): void
    {
        if (!$quote->isDateExpired() || in_array($quote->status, [Quote::STATUS_ACCEPTED, Quote::STATUS_REJECTED], true)) {
            return;
        }

        $quote->forceFill(['status' => Quote::STATUS_EXPIRED])->save();
    }
}
