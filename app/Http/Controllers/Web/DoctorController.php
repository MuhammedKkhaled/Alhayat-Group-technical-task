<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function __construct(private readonly DoctorRepositoryInterface $doctors)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Doctors/Index', [
            'doctors' => $this->doctors->activeDoctors(),
        ]);
    }

    public function show(int $doctor): Response
    {
        return Inertia::render('Doctors/Show', [
            'doctor' => $this->doctors->findActiveWithServices($doctor),
        ]);
    }
}
