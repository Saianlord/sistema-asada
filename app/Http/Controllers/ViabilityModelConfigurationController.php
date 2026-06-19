<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateViabilityModelConfigurationRequest;
use App\Models\ViabilityModelConfiguration;
use App\Models\ProjectEvaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ViabilityModelConfigurationController extends Controller
{
    public function edit(): View
    {
        $config = ViabilityModelConfiguration::getActive();
        return view('viability_config.edit', compact('config'));
    }

    public function update(UpdateViabilityModelConfigurationRequest $request): RedirectResponse
    {
        ViabilityModelConfiguration::clearCache();
        $config = ViabilityModelConfiguration::getActive();
        $config->update($request->validated());

        ViabilityModelConfiguration::clearCache();

        ProjectEvaluation::chunk(100, function ($evaluations) {
            foreach ($evaluations as $evaluation) {
                $evaluation->save();
            }
        });

        return redirect()->route('viability-config.edit')
            ->with('success', 'Configuración del modelo de viabilidad actualizada exitosamente y evaluaciones recalculadas.');
    }
}
