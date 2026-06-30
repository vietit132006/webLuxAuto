<?php

namespace App\Services;

use App\Models\Quote;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

class QuotePdfService
{
    public function download(Quote $quote): Response
    {
        $quote->loadMissing(['customer', 'car.modelInfo.brand', 'user', 'quotePromotions.promotion']);

        $html = view('admin.quotes.pdf', compact('quote'))->render();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $quote->quote_code . '.pdf"',
        ]);
    }
}
