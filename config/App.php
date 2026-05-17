<?php

namespace Config;

class App
{
    public static array $middlewareAliases = [
        'auth' => \App\Middleware\Authenticate::class,
        'admin' => \App\Middleware\AdminOnly::class,
        'doctor' => \App\Middleware\DoctorOnly::class,
        'secretary' => \App\Middleware\SecretaryOnly::class,
        'patient' => \App\Middleware\PatientOnly::class,
    ];
}
