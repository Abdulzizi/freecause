## Phase 1 Page Map

### Home
Legacy: tpl2/______.php
New:   resources/views/pages/home.blade.php
Route: /{locale}

### Explore petitions
Legacy: tpl2/______.php
New:   resources/views/pages/petitions/index.blade.php
Route: /{locale}/petitions

### Petition detail
Legacy: tpl2/______.php
New:   resources/views/pages/petitions/show.blade.php
Route: /{locale}/petitions/{slug}

### Create petition (start)
Legacy: tpl2/______.php
New:   resources/views/pages/petitions/create.blade.php
Route: /{locale}/start (or /{locale}/petitions/create)

### Sign petition flow
Legacy: tpl2/______.php
New:   resources/views/pages/signatures/form.blade.php
Route: /{locale}/petitions/{slug}/sign
