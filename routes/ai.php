<?php

use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Route::prefix('mcp')
    ->group(function () {
        Mcp::web('award', \App\Mcp\Servers\AwardServer::class);
        Mcp::web('basic', \App\Mcp\Servers\BasicServer::class);
        Mcp::web('certificate', \App\Mcp\Servers\CertificateServer::class);
        Mcp::web('education', \App\Mcp\Servers\EducationServer::class);
        Mcp::web('interest', \App\Mcp\Servers\InterestServer::class);
        Mcp::web('iso', \App\Mcp\Servers\IsoServer::class);
        Mcp::web('language', \App\Mcp\Servers\LanguageServer::class);
        Mcp::web('project', \App\Mcp\Servers\ProjectServer::class);
        Mcp::web('publication', \App\Mcp\Servers\PublicationServer::class);
        Mcp::web('reference', \App\Mcp\Servers\ReferenceServer::class);
        Mcp::web('skill', \App\Mcp\Servers\SkillServer::class);
        Mcp::web('volunteer', \App\Mcp\Servers\VolunteerServer::class);
        Mcp::web('work', \App\Mcp\Servers\WorkServer::class);
    });
