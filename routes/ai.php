<?php

use Laravel\Mcp\Facades\Mcp;
use Illuminate\Support\Facades\Route;

// Register MCP servers by module and restrict access via AllowMcpProvider middleware.
Route::middleware([\App\Http\Middleware\AllowMcpProvider::class])->group(function () {
	Mcp::web('/mcp/auth', \App\Mcp\Servers\AuthenticationServer::class);
	Mcp::web('/mcp/award', \App\Mcp\Servers\AwardServer::class);
	Mcp::web('/mcp/basic', \App\Mcp\Servers\BasicServer::class);
	Mcp::web('/mcp/certificate', \App\Mcp\Servers\CertificateServer::class);
	Mcp::web('/mcp/education', \App\Mcp\Servers\EducationServer::class);
	Mcp::web('/mcp/interest', \App\Mcp\Servers\InterestServer::class);
	Mcp::web('/mcp/iso', \App\Mcp\Servers\IsoServer::class);
	Mcp::web('/mcp/language', \App\Mcp\Servers\LanguageServer::class);
	Mcp::web('/mcp/project', \App\Mcp\Servers\ProjectServer::class);
	Mcp::web('/mcp/publication', \App\Mcp\Servers\PublicationServer::class);
	Mcp::web('/mcp/reference', \App\Mcp\Servers\ReferenceServer::class);
	Mcp::web('/mcp/skill', \App\Mcp\Servers\SkillServer::class);
	Mcp::web('/mcp/volunteer', \App\Mcp\Servers\VolunteerServer::class);
	Mcp::web('/mcp/work', \App\Mcp\Servers\WorkServer::class);
});