<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('customer.login');
        }

        $addresses = Auth::user()->addresses()->latest()->get();
        return view('frontend.addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please login'], 401);
        }

        $request->validate([
            'type' => 'required|string|in:Home,Work,Other',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|regex:/^[0-9]{10}$/',
            'house_no' => 'required|string|max:255',
            'street_address' => 'required|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'pincode' => 'required|string|regex:/^[0-9]{6}$/',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'formatted_address' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        $isFirst = Auth::user()->addresses()->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirst;

        if ($isDefault) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        $formatted = $request->formatted_address ?: trim("{$request->house_no}, {$request->street_address}" . ($request->landmark ? ", Near {$request->landmark}" : "") . ", {$request->city}, Gujarat - {$request->pincode}");

        $address = Address::create([
            'user_id' => Auth::id(),
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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Address saved successfully!',
                'address' => $address,
            ]);
        }

        return back()->with('success', 'Address saved successfully!');
    }

    public function update(Request $request, Address $address)
    {
        if (!Auth::check() || $address->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'type' => 'required|string|in:Home,Work,Other',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|regex:/^[0-9]{10}$/',
            'house_no' => 'required|string|max:255',
            'street_address' => 'required|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'pincode' => 'required|string|regex:/^[0-9]{6}$/',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($request->boolean('is_default')) {
            Auth::user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->is_default = true;
        }

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

        return back()->with('success', 'Address updated successfully!');
    }

    public function setDefault(Address $address)
    {
        if (!Auth::check() || $address->user_id !== Auth::id()) {
            abort(403);
        }

        Auth::user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Default address updated.');
    }

    public function destroy(Address $address)
    {
        if (!Auth::check() || $address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();
        return back()->with('success', 'Address deleted successfully.');
    }
}
