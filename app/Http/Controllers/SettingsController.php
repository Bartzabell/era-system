<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Hotline;
use App\Models\Emergency;
use App\Models\Incident;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    private function sharedData(): array
    {
        return [
            'hotlines'   => Hotline::orderBy('hotline_name')->get(),
            'emergencies' => Emergency::with('incidents')->orderBy('emergency_name')->get(),
            'incidents'  => Incident::with('emergency')->orderBy('incident_name')->get(),
        ];
    }

    public function index()
    {
        return Inertia::render('SystemSettings/Index', $this->sharedData());
    }

    // ── Hotline ──────────────────────────────────────────────────────────────

    public function storeHotline(Request $request)
    {
        $request->validate([
            'hotline_name' => 'required|string|max:255',
            'hotline_no'   => 'required|string|max:50',
            'description'  => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            Hotline::create([
                ...$request->only('hotline_name', 'hotline_no', 'description'),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->route('system-settings.index')->with('success', 'Hotline created successfully.');
    }

    public function updateHotline(Request $request, Hotline $hotline)
    {
        $request->validate([
            'hotline_name' => 'required|string|max:255',
            'hotline_no'   => 'required|string|max:50',
            'description'  => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $hotline) {
            $hotline->update([
                ...$request->only('hotline_name', 'hotline_no', 'description'),
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->route('system-settings.index')->with('success', 'Hotline updated successfully.');
    }

    public function destroyHotline(Hotline $hotline)
    {
        DB::transaction(function () use ($hotline) {
            $hotline->update(['deleted_by' => Auth::id()]);
            $hotline->delete();
        });

        return redirect()->route('system-settings.index')->with('success', 'Hotline deleted successfully.');
    }

    // ── Emergency ────────────────────────────────────────────────────────────

    public function storeEmergency(Request $request)
    {
        $request->validate([
            'emergency_name' => 'required|string|max:255',
            'definition'     => 'nullable|string',
            'severity_level' => 'nullable|string|max:10',
        ]);

        DB::transaction(function () use ($request) {
            Emergency::create([
                ...$request->only('emergency_name', 'definition', 'severity_level'),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->route('system-settings.index')->with('success', 'Emergency created successfully.');
    }

    public function updateEmergency(Request $request, Emergency $emergency)
    {
        $request->validate([
            'emergency_name' => 'required|string|max:255',
            'definition'     => 'nullable|string',
            'severity_level' => 'nullable|string|max:10',
        ]);

        DB::transaction(function () use ($request, $emergency) {
            $emergency->update([
                ...$request->only('emergency_name', 'definition', 'severity_level'),
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->route('system-settings.index')->with('success', 'Emergency updated successfully.');
    }

    public function destroyEmergency(Emergency $emergency)
    {
        DB::transaction(function () use ($emergency) {
            $emergency->update(['deleted_by' => Auth::id()]);
            $emergency->delete();
        });

        return redirect()->route('system-settings.index')->with('success', 'Emergency deleted successfully.');
    }

    // ── Incident ─────────────────────────────────────────────────────────────

    public function storeIncident(Request $request)
    {
        $request->validate([
            'incident_name'  => 'required|string|max:255',
            'definition'     => 'nullable|string',
            'base_severity'  => 'nullable|numeric|min:0|max:10',
            'base_time'      => 'nullable|numeric|min:0',
            'base_resources' => 'nullable|numeric|min:0',
            'base_secondary' => 'nullable|numeric|min:0',
            'emergency_id'   => 'required|exists:emergencies,id',
        ]);

        DB::transaction(function () use ($request) {
            Incident::create([
                ...$request->only('incident_name', 'definition', 'base_severity', 'base_time', 'base_resources', 'base_secondary', 'emergency_id'),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->route('system-settings.index')->with('success', 'Incident created successfully.');
    }

    public function updateIncident(Request $request, Incident $incident)
    {
        $request->validate([
            'incident_name'  => 'required|string|max:255',
            'definition'     => 'nullable|string',
            'base_severity'  => 'nullable|numeric|min:0|max:10',
            'base_time'      => 'nullable|numeric|min:0',
            'base_resources' => 'nullable|numeric|min:0',
            'base_secondary' => 'nullable|numeric|min:0',
            'emergency_id'   => 'required|exists:emergencies,id',
        ]);

        DB::transaction(function () use ($request, $incident) {
            $incident->update([
                ...$request->only('incident_name', 'definition', 'base_severity', 'base_time', 'base_resources', 'base_secondary', 'emergency_id'),
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()->route('system-settings.index')->with('success', 'Incident updated successfully.');
    }

    public function destroyIncident(Incident $incident)
    {
        DB::transaction(function () use ($incident) {
            $incident->update(['deleted_by' => Auth::id()]);
            $incident->delete();
        });

        return redirect()->route('system-settings.index')->with('success', 'Incident deleted successfully.');
    }
}
