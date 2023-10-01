<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Http\Resources\V1\InvoiceResource;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return InvoiceResource::collection(Invoice::with('user')->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'type' => 'required|max:1',
            'paid' => 'required|numeric|between:0,1',
            'value' => 'required|numeric|between:1,9999.99',
            'payment_date' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->error('Data invalid', 422, $validator->errors());
        }

        $created = Invoice::create($validator->validated());

        if (!$created) {
            return $this->error('Invoice not created', 422);
        }

        return $this->response('Invoice created', 201, new InvoiceResource($created->load('user')));
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        return new InvoiceResource($invoice);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'type' => 'required|max:1',
            'paid' => 'required|numeric|between:0,1',
            'value' => 'required|numeric',
            'payment_date' => 'nullable|date_format:Y-m-d H:i:s',

        ]);

        if ($validator->fails()) {
            return $this->error('Data invalid', 422, $validator->errors());
        }

        $validated = $validator->validated();

        $updated = $invoice->update([
            'user_id' => $validated['user_id'],
            'type' => $validated['type'],
            'paid' => $validated['paid'],
            'value' => $validated['value'],
            'payment_date' => $validated['paid'] ? $validated['payment_date'] : null,
        ]);

        if (!$updated) {
            return $this->error('Invoice not updated', 400);
        }

        return $this->response('Invoice updated', 202, new InvoiceResource($invoice->load('user')));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
