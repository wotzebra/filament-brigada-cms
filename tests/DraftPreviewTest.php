<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Oddvalue\LaravelDrafts\Facades\LaravelDrafts;
use Wotz\FilamentBrigadaCms\Http\Middleware\DisableDraftPreview;

it('turns off draft preview for every panel request', function () {
    /*
     * Preview mode is a session flag set on the front end, and it outlives the tab it was
     * turned on in. Left on inside the panel, a resource loads the unpublished revision of
     * a record and the editor unknowingly edits the draft of the thing they opened.
     */
    LaravelDrafts::previewMode();

    (new DisableDraftPreview)->handle(Request::create('/admin/articles'), fn () => new Response);

    expect(LaravelDrafts::isPreviewModeEnabled())->toBeFalse();
});

it('passes the request on untouched', function () {
    $response = (new DisableDraftPreview)->handle(
        Request::create('/admin/articles'),
        fn (Request $request) => new Response($request->path()),
    );

    expect($response->getContent())->toBe('admin/articles');
});
