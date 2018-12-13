<?php

Breadcrumbs::for('admin.dashboard', function ($trail) {
    $trail->push(__('strings.backend.dashboard.title'), route('admin.dashboard'));
});



//*****Do Not Delete Me

require __DIR__.'/auth.php';
require __DIR__.'/log-viewer.php';
