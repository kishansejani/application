<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->latest()->get();
        return response()->json(['status' => true, 'data' => $addresses]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:Home,Work,Other',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|regex:/^[0-9]{10}$/',
            'house_no' => 'required|string',
            'street_address' => 'required|string',
            'landmark' => 'nullable|string',
            'city' => 'required|string',
            'pincode' => 'required|string|regex:/^[0-9]{6}$/',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'formatted_address' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $isFirst = $user->addresses()->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirst;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $formatted = $request->formatted_address ?: trim("{$request->house_no}, {$request->street_address}" . ($request->landmark ? ", Near {$request->landmark}" : "") . ", {$request->city}, Gujarat - {$request->pincode}");

        $address = Address::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'recipient_name' => $request->recipient_name,
            'recipient_phone' => $request->recipient_phone,
            'house_no' => $request->house_no,
            'street_address' => $request->street_address,
            'landmark' => $request->landmark,
            'city' => $request->city,
            'state' => 'Gujarat',
            'pincode' => $request->pincode,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'formatted_address' => $formatted,
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Address saved successfully.',
            'data' => $address,
        ]);
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'type' => 'required|string|in:Home,Work,Other',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|regex:/^[0-9]{10}$/',
            'house_no' => 'required|string',
            'street_address' => 'required|string',
            'landmark' => 'nullable|string',
            'city' => 'required|string',
            'pincode' => 'required|string|regex:/^[0-9]{6}$/',
        ]);

        $formatted = trim("{$request->house_no}, {$request->street_address}" . ($request->landmark ? ", Near {$request->landmark}" : "") . ", {$request->city}, Gujarat - {$request->pincode}");

        $address->update([
            'type' => $request->type,
            'recipient_name' => $request->recipient_name,
            'recipient_phone' => $request->recipient_phone,
            'house_no' => $request->house_no,
            'street_address' => $request->street_address,
            'landmark' => $request->landmark,
            'city' => $request->city,
            'pincode' => $request->pincode,
            'latitude' => $request->latitude ?? $address->latitude,
            'longitude' => $request->longitude ?? $address->longitude,
            'formatted_address' => $formatted,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Address updated successfully.',
            'data' => $address,
        ]);
    }

    public function setDefault(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(['status' => true, 'message' => 'Default address set.']);
    }

    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $address->delete();
        return response()->json(['status' => true, 'message' => 'Address deleted.']);
    }
}
