<?php

use App\Mcp\Servers\AwardServer;
use App\Mcp\Servers\BasicServer;
use App\Mcp\Servers\CertificateServer;
use App\Mcp\Servers\EducationServer;
use App\Mcp\Servers\InterestServer;
use App\Mcp\Servers\IsoServer;
use App\Mcp\Servers\LanguageServer;
use App\Mcp\Servers\ProjectServer;
use App\Mcp\Servers\PublicationServer;
use App\Mcp\Servers\ReferenceServer;
use App\Mcp\Servers\SkillServer;
use App\Mcp\Servers\VolunteerServer;
use App\Mcp\Servers\WorkServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Route::prefix('mcp')
    ->group(function () {
        Mcp::web('award', AwardServer::class);
        Mcp::web('basic', BasicServer::class);
        Mcp::web('certificate', CertificateServer::class);
        Mcp::web('education', EducationServer::class);
        Mcp::web('interest', InterestServer::class);
        Mcp::web('iso', IsoServer::class);
        Mcp::web('language', LanguageServer::class);
        Mcp::web('project', ProjectServer::class);
        Mcp::web('publication', PublicationServer::class);
        Mcp::web('reference', ReferenceServer::class);
        Mcp::web('skill', SkillServer::class);
        Mcp::web('volunteer', VolunteerServer::class);
        Mcp::web('work', WorkServer::class);
    });
