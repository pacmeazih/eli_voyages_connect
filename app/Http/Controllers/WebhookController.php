<?php

namespace App\Http\Controllers;

use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected ContractService $contractService
    ) {}

    /**
     * Handle DocuSeal webhook
     */
    public function docuseal(Request $request)
    {
        try {
            // Validate webhook signature
            $signature = $request->header('X-Webhook-Signature');
            $payload = $request->getContent();
            
            if (!$this->contractService->validateWebhookSignature($payload, $signature)) {
                Log::warning('Invalid DocuSeal webhook signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Process the webhook
            $this->contractService->processWebhook($request->all());

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('DocuSeal webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }
}
