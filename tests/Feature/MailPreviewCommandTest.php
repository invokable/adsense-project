<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MailPreviewCommandTest extends TestCase
{
    public function test_mail_preview_command_generates_html_files(): void
    {
        // Clean up any existing preview files
        $storageDir = storage_path('framework/testing');
        $jaFilePath = $storageDir.'/adsense-mail-preview-ja.html';
        $enFilePath = $storageDir.'/adsense-mail-preview-en.html';

        if (file_exists($jaFilePath)) {
            unlink($jaFilePath);
        }
        if (file_exists($enFilePath)) {
            unlink($enFilePath);
        }

        // Execute the command
        $this->artisan('mail:preview')
            ->expectsOutput('Email preview HTML files saved:')
            ->expectsOutput('Japanese: '.$jaFilePath)
            ->expectsOutput('English: '.$enFilePath)
            ->expectsOutput('Open these files in your browser to preview the email.')
            ->assertExitCode(0);

        // Assert that both HTML files were created
        $this->assertTrue(file_exists($jaFilePath), 'Japanese preview file should exist');
        $this->assertTrue(file_exists($enFilePath), 'English preview file should exist');

        // Assert that files contain HTML content
        $jaContent = file_get_contents($jaFilePath);
        $enContent = file_get_contents($enFilePath);

        $this->assertStringContainsString('<html', $jaContent, 'Japanese file should contain HTML');
        $this->assertStringContainsString('<html', $enContent, 'English file should contain HTML');
        $this->assertStringContainsString('AdSense', $jaContent, 'Japanese file should contain AdSense content');
        $this->assertStringContainsString('AdSense', $enContent, 'English file should contain AdSense content');
    }
}
