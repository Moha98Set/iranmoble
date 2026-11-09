<?php

/*
 * Override for the package's own Persian file, which leaves nav.group as the
 * untranslated "Filament Shield" and so shows an English item in the middle of
 * a Persian menu. Laravel merges this on top of the package translations, so
 * only the keys that need changing belong here.
 */

return [
    'nav.group' => 'دسترسی‌ها',
];
