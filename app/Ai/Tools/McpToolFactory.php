<?php

namespace App\Ai\Tools;

use App\Mcp\Servers\AwardServer;
use App\Mcp\Servers\BasicServer;
use App\Mcp\Servers\CertificateServer;
use App\Mcp\Servers\EducationServer;
use App\Mcp\Servers\InterestServer;
use App\Mcp\Servers\LanguageServer;
use App\Mcp\Servers\ProjectServer;
use App\Mcp\Servers\PublicationServer;
use App\Mcp\Servers\ReferenceServer;
use App\Mcp\Servers\SkillServer;
use App\Mcp\Servers\VolunteerServer;
use App\Mcp\Servers\WorkServer;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class McpToolFactory
{
    /**
     * Get all available MCP tools for the resume agent.
     * These tools allow the agent to query resume data from various MCP servers.
     */
    public static function getAllTools(): array
    {
        return [
            new BasicInfoTool,
            new WorkExperienceTool,
            new EducationTool,
            new SkillsTool,
            new ProjectsTool,
            new CertificatesTool,
            new PublicationsTool,
            new AwardsTool,
            new LanguagesTool,
            new ReferencesTool,
            new InterestsTool,
            new VolunteerTool,
        ];
    }
}

class BasicInfoTool implements Tool
{
    public function description(): string
    {
        return 'Get basic profile information like name, summary, contact details, and location';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new BasicServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No basic profile information found.';
            }

            $basic = $result['data'][0] ?? [];

            return $this->formatBasicInfo($basic);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving basic info: '.$e->getMessage());

            return "Error retrieving basic info: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatBasicInfo(array $basic): string
    {
        $lines = ['Basic Profile Information:'];
        if (! empty($basic['name'])) {
            $lines[] = "Name: {$basic['name']}";
        }
        if (! empty($basic['summary'])) {
            $lines[] = "Summary: {$basic['summary']}";
        }
        if (! empty($basic['email'])) {
            $lines[] = "Email: {$basic['email']}";
        }
        if (! empty($basic['phone'])) {
            $lines[] = "Phone: {$basic['phone']}";
        }
        if (! empty($basic['location'])) {
            $lines[] = "Location: {$basic['location']}";
        }
        if (! empty($basic['url'])) {
            $lines[] = "Website: {$basic['url']}";
        }

        return implode("\n", $lines);
    }
}

class WorkExperienceTool implements Tool
{
    public function description(): string
    {
        return 'Get detailed work experience including positions, companies, dates, and descriptions';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new WorkServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No work experience found.';
            }

            return $this->formatWorkExperience($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving work experience: '.$e->getMessage());

            return "Error retrieving work experience: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatWorkExperience(array $works): string
    {
        $lines = ['Work Experience:'];
        foreach ($works as $work) {
            if (! empty($work['position'])) {
                $lines[] = "• {$work['position']}";
            }
            if (! empty($work['name'])) {
                $lines[] = "  Company: {$work['name']}";
            }
            if (! empty($work['startDate'])) {
                $endDate = ! empty($work['endDate']) ? $work['endDate'] : 'Present';
                $lines[] = "  Period: {$work['startDate']} to {$endDate}";
            }
            if (! empty($work['summary'])) {
                $lines[] = "  Summary: {$work['summary']}";
            }
        }

        return implode("\n", $lines);
    }
}

class EducationTool implements Tool
{
    public function description(): string
    {
        return 'Get educational background including degree, institution, and field of study';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new EducationServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No education information found.';
            }

            return $this->formatEducation($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving education: '.$e->getMessage());

            return "Error retrieving education: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatEducation(array $educations): string
    {
        $lines = ['Education:'];
        foreach ($educations as $edu) {
            if (! empty($edu['studyType']) && ! empty($edu['area'])) {
                $lines[] = "• {$edu['studyType']} in {$edu['area']}";
            }
            if (! empty($edu['institution'])) {
                $lines[] = "  Institution: {$edu['institution']}";
            }
            if (! empty($edu['startDate'])) {
                $endDate = ! empty($edu['endDate']) ? $edu['endDate'] : 'Present';
                $lines[] = "  Period: {$edu['startDate']} to {$endDate}";
            }
        }

        return implode("\n", $lines);
    }
}

class SkillsTool implements Tool
{
    public function description(): string
    {
        return 'Get list of skills with proficiency levels and keywords';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new SkillServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No skills found.';
            }

            return $this->formatSkills($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving skills: '.$e->getMessage());

            return "Error retrieving skills: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatSkills(array $skills): string
    {
        $lines = ['Skills:'];
        foreach ($skills as $skill) {
            $name = $skill['name'] ?? 'Unknown Skill';
            $level = $skill['level'] ?? '';
            $line = "• {$name}";
            if (! empty($level)) {
                $line .= " ({$level})";
            }
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }
}

class ProjectsTool implements Tool
{
    public function description(): string
    {
        return 'Get information about projects including descriptions and highlights';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new ProjectServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No projects found.';
            }

            return $this->formatProjects($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving projects: '.$e->getMessage());

            return "Error retrieving projects: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatProjects(array $projects): string
    {
        $lines = ['Projects:'];
        foreach ($projects as $project) {
            if (! empty($project['name'])) {
                $lines[] = "• {$project['name']}";
            }
            if (! empty($project['description'])) {
                $lines[] = "  Description: {$project['description']}";
            }
            if (! empty($project['highlights']) && is_array($project['highlights'])) {
                $lines[] = '  Highlights: '.implode(', ', $project['highlights']);
            }
        }

        return implode("\n", $lines);
    }
}

class CertificatesTool implements Tool
{
    public function description(): string
    {
        return 'Get certifications and credentials including issuer and date';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new CertificateServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No certifications found.';
            }

            return $this->formatCertificates($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving certificates: '.$e->getMessage());

            return "Error retrieving certificates: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatCertificates(array $certificates): string
    {
        $lines = ['Certifications:'];
        foreach ($certificates as $cert) {
            if (! empty($cert['name'])) {
                $lines[] = "• {$cert['name']}";
            }
            if (! empty($cert['issuer'])) {
                $lines[] = "  Issuer: {$cert['issuer']}";
            }
            if (! empty($cert['date'])) {
                $lines[] = "  Date: {$cert['date']}";
            }
        }

        return implode("\n", $lines);
    }
}

class PublicationsTool implements Tool
{
    public function description(): string
    {
        return 'Get publications and articles with titles and publishers';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new PublicationServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No publications found.';
            }

            return $this->formatPublications($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving publications: '.$e->getMessage());

            return "Error retrieving publications: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatPublications(array $pubs): string
    {
        $lines = ['Publications:'];
        foreach ($pubs as $pub) {
            if (! empty($pub['name'])) {
                $lines[] = "• {$pub['name']}";
            }
            if (! empty($pub['publisher'])) {
                $lines[] = "  Publisher: {$pub['publisher']}";
            }
            if (! empty($pub['releaseDate'])) {
                $lines[] = "  Date: {$pub['releaseDate']}";
            }
        }

        return implode("\n", $lines);
    }
}

class AwardsTool implements Tool
{
    public function description(): string
    {
        return 'Get awards and honors';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new AwardServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No awards found.';
            }

            return $this->formatAwards($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving awards: '.$e->getMessage());

            return "Error retrieving awards: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatAwards(array $awards): string
    {
        $lines = ['Awards:'];
        foreach ($awards as $award) {
            if (! empty($award['title'])) {
                $lines[] = "• {$award['title']}";
            }
            if (! empty($award['awarder'])) {
                $lines[] = "  Awarder: {$award['awarder']}";
            }
            if (! empty($award['date'])) {
                $lines[] = "  Date: {$award['date']}";
            }
        }

        return implode("\n", $lines);
    }
}

class LanguagesTool implements Tool
{
    public function description(): string
    {
        return 'Get languages spoken and proficiency levels';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new LanguageServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No languages found.';
            }

            return $this->formatLanguages($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving languages: '.$e->getMessage());

            return "Error retrieving languages: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatLanguages(array $languages): string
    {
        $lines = ['Languages:'];
        foreach ($languages as $lang) {
            if (! empty($lang['language'])) {
                $fluency = ! empty($lang['fluency']) ? " ({$lang['fluency']})" : '';
                $lines[] = "• {$lang['language']}{$fluency}";
            }
        }

        return implode("\n", $lines);
    }
}

class ReferencesTool implements Tool
{
    public function description(): string
    {
        return 'Get professional references and recommendations';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new ReferenceServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No references found.';
            }

            return $this->formatReferences($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving references: '.$e->getMessage());

            return "Error retrieving references: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatReferences(array $refs): string
    {
        $lines = ['References:'];
        foreach ($refs as $ref) {
            if (! empty($ref['name'])) {
                $lines[] = "• {$ref['name']}";
            }
            if (! empty($ref['reference'])) {
                $lines[] = "  {$ref['reference']}";
            }
        }

        return implode("\n", $lines);
    }
}

class InterestsTool implements Tool
{
    public function description(): string
    {
        return 'Get personal interests and hobbies';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new InterestServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No interests found.';
            }

            return $this->formatInterests($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving interests: '.$e->getMessage());

            return "Error retrieving interests: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatInterests(array $interests): string
    {
        $lines = ['Interests:'];
        foreach ($interests as $interest) {
            if (! empty($interest['name'])) {
                $lines[] = "• {$interest['name']}";
            }
        }

        return implode("\n", $lines);
    }
}

class VolunteerTool implements Tool
{
    public function description(): string
    {
        return 'Get volunteer work and community service information';
    }

    public function handle(Request $request): string
    {
        try {
            $server = new VolunteerServer;
            $result = $server->index();

            if (empty($result['data'])) {
                return 'No volunteer experience found.';
            }

            return $this->formatVolunteer($result['data']);
        } catch (\Throwable $e) {
            Log::debug('Error retrieving volunteer experience: '.$e->getMessage());

            return "Error retrieving volunteer experience: {$e->getMessage()}";
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    private function formatVolunteer(array $volunteers): string
    {
        $lines = ['Volunteer Work:'];
        foreach ($volunteers as $vol) {
            if (! empty($vol['position'])) {
                $lines[] = "• {$vol['position']}";
            }
            if (! empty($vol['organization'])) {
                $lines[] = "  Organization: {$vol['organization']}";
            }
            if (! empty($vol['startDate'])) {
                $endDate = ! empty($vol['endDate']) ? $vol['endDate'] : 'Present';
                $lines[] = "  Period: {$vol['startDate']} to {$endDate}";
            }
            if (! empty($vol['summary'])) {
                $lines[] = "  Summary: {$vol['summary']}";
            }
        }

        return implode("\n", $lines);
    }
}
