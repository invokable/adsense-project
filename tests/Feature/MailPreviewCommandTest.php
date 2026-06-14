<?php

test('mail preview command generates html files', function () {
    $storageDir = storage_path('framework/testing');
    $jaFilePath = $storageDir.'/adsense-mail-preview-ja.html';
    $enFilePath = $storageDir.'/adsense-mail-preview-en.html';

    // Clean up any existing files
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
    expect(file_exists($jaFilePath))->toBeTrue('Japanese preview file should exist');
    expect(file_exists($enFilePath))->toBeTrue('English preview file should exist');

    // Assert that files contain HTML content
    $jaContent = file_get_contents($jaFilePath);
    $enContent = file_get_contents($enFilePath);

    $this->assertStringContainsString('<html', $jaContent, 'Japanese file should contain HTML');
    $this->assertStringContainsString('<html', $enContent, 'English file should contain HTML');
    $this->assertStringContainsString('AdSense', $jaContent, 'Japanese file should contain AdSense content');
    $this->assertStringContainsString('AdSense', $enContent, 'English file should contain AdSense content');
});
