<?php
// What the admin can edit. Each section edits some keys of one top-level content object.
// Field: [key, type, label, extra...]. Types: text, textarea, checkbox, number, select, file, list, group.

const HINT_INLINE = '**félkövér** · [szögletes zárójel] = kiemelt (piros) szöveg';
const HINT_PARAS  = 'Üres sor = új bekezdés. ' . HINT_INLINE;
const HINT_LINES  = 'Soronként egy elem.';

function admin_sections(): array {
    return [
        'home' => [
            'title' => 'Főoldal',
            'intro' => 'A főoldal szövegei, felülről lefelé. A kérdéseket (GYIK) külön menüpontban találod.',
            'root'  => 'home',
            'view'  => './',
            'fields' => [
                ['_h', 'heading', 'Nyitó rész (hero)'],
                ['hero_eyebrow', 'text', 'Kis felirat a cím fölött'],
                ['hero_title', 'text', 'Főcím', 'hint' => 'A [szögletes zárójelbe] tett rész pirossal jelenik meg.'],
                ['hero_lede', 'textarea', 'Bevezető szöveg', 'rows' => 3, 'hint' => HINT_INLINE],
                ['hero_button', 'text', 'Piros gomb felirata'],
                ['hero_button2', 'text', 'Második gomb felirata'],

                ['_h', 'heading', 'Bemutatkozás'],
                ['about_title', 'text', 'Cím'],
                ['about_mission', 'textarea', 'Küldetés (idézetként jelenik meg)', 'rows' => 3],
                ['about_text', 'textarea', 'Bemutatkozó szöveg', 'rows' => 10, 'hint' => HINT_PARAS],
                ['credentials', 'textarea', 'Szakterület címkék', 'rows' => 5, 'hint' => HINT_LINES],

                ['_h', 'heading', 'Szolgáltatások'],
                ['services_title', 'text', 'Cím'],
                ['services_lede', 'textarea', 'Bevezető', 'rows' => 2],
                ['services', 'list', 'Szolgáltatás kártyák', 'item' => 'title', 'add' => 'Új szolgáltatás', 'fields' => [
                    ['title', 'text', 'Név'],
                    ['ref', 'text', 'Szabványhivatkozás (kis kék betűs sor)'],
                    ['text', 'textarea', 'Rövid leírás', 'rows' => 2],
                    ['bullets', 'textarea', 'Felsorolás', 'rows' => 4, 'hint' => HINT_LINES],
                ]],
                ['services_note', 'textarea', 'Megjegyzés a kártyák alatt', 'rows' => 2],

                ['_h', 'heading', 'A 8D módszer'],
                ['method_title', 'text', 'Cím'],
                ['method_lede', 'textarea', 'Bevezető', 'rows' => 2],
                ['method_steps', 'list', 'Lépések', 'item' => 'title', 'add' => 'Új lépés', 'fields' => [
                    ['code', 'text', 'Jel (pl. D4)'],
                    ['title', 'text', 'Cím'],
                    ['text', 'textarea', 'Leírás', 'rows' => 2],
                    ['highlight', 'checkbox', 'Kiemelés pirossal („True north” címkével)'],
                ]],

                ['_h', 'heading', 'Ingyenes anyagok blokk'],
                ['free_title', 'text', 'Cím'],
                ['free_lede', 'textarea', 'Bevezető', 'rows' => 2],

                ['_h', 'heading', 'Szabványok sáv'],
                ['standards', 'list', 'Szabványok', 'item' => 'name', 'add' => 'Új szabvány', 'fields' => [
                    ['name', 'text', 'Szabvány'],
                    ['desc', 'text', 'Rövid leírás'],
                ]],

                ['_h', 'heading', 'Záró felhívás (alul)'],
                ['cta_title', 'text', 'Cím'],
                ['cta_text', 'textarea', 'Szöveg', 'rows' => 2],
            ],
        ],
        'faq' => [
            'title' => 'Kérdések (GYIK)',
            'intro' => 'A főoldal alján megjelenő gyakori kérdések. A sorrendet a nyilakkal módosíthatod.',
            'root'  => 'home',
            'view'  => './#faq',
            'fields' => [
                ['faq', 'list', 'Kérdések', 'item' => 'q', 'add' => 'Új kérdés', 'fields' => [
                    ['q', 'text', 'Kérdés'],
                    ['a', 'textarea', 'Válasz', 'rows' => 4, 'hint' => HINT_PARAS],
                ]],
            ],
        ],
        'resources' => [
            'title' => 'Erőforrások',
            'intro' => 'A letölthető vagy kérhető dokumentumok és a tréningek. Fájlt előbb a „Fájlok” menüben tölts fel, utána itt választhatod ki.',
            'root'  => 'resources',
            'view'  => 'resources.php',
            'fields' => [
                ['_h', 'heading', 'Oldal szövegei'],
                ['intro', 'textarea', 'Bevezető a fejlécben', 'rows' => 3],
                ['how_to', 'textarea', 'Tájékoztató doboz', 'rows' => 3, 'hint' => HINT_INLINE],

                ['_h', 'heading', 'Dokumentumok'],
                ['items', 'list', 'Összes dokumentum', 'item' => 'title', 'sub' => 'code', 'add' => 'Új dokumentum', 'fields' => [
                    ['visible', 'checkbox', 'Megjelenik az oldalon', 'default' => true],
                    ['category', 'select', 'Kategória', 'options' => [
                        'templates' => 'SOP sablonok', 'logs' => 'Naplók és űrlapok', 'materials' => 'Oktatási anyagok',
                    ]],
                    ['code', 'text', 'Dokumentumszám (pl. 8DN-SOP-07)'],
                    ['title', 'text', 'Cím'],
                    ['description', 'textarea', 'Leírás', 'rows' => 3],
                    ['format', 'text', 'Formátum (pl. DOCX, XLSX, PDF)'],
                    ['ref', 'text', 'Szabványhivatkozás'],
                    ['revision', 'text', 'Verzió (pl. Rev. A)'],
                    ['file', 'file', 'Csatolt fájl'],
                    ['public_download', 'checkbox', 'Szabadon letölthető (ha nincs bejelölve, a látogató űrlapon kéri)'],
                ]],

                ['_h', 'heading', 'Kategóriák címei'],
                ['categories', 'group', '', 'fields' => [
                    ['templates', 'group', 'SOP sablonok', 'fields' => [['title', 'text', 'Cím'], ['text', 'textarea', 'Leírás', 'rows' => 2]]],
                    ['logs', 'group', 'Naplók és űrlapok', 'fields' => [['title', 'text', 'Cím'], ['text', 'textarea', 'Leírás', 'rows' => 2]]],
                    ['materials', 'group', 'Oktatási anyagok', 'fields' => [['title', 'text', 'Cím'], ['text', 'textarea', 'Leírás', 'rows' => 2]]],
                ]],

                ['_h', 'heading', 'Tréningek'],
                ['trainings_title', 'text', 'Cím'],
                ['trainings_text', 'textarea', 'Bevezető', 'rows' => 2],
                ['trainings', 'list', 'Tréningek', 'item' => 'title', 'add' => 'Új tréning', 'fields' => [
                    ['kicker', 'text', 'Kis felirat (pl. Online)'],
                    ['title', 'text', 'Cím'],
                    ['text', 'textarea', 'Leírás', 'rows' => 3],
                    ['topics', 'text', 'Témák'],
                    ['format', 'text', 'Forma'],
                    ['cost', 'text', 'Díj'],
                    ['button', 'text', 'Gomb felirata'],
                    ['topic_key', 'select', 'Az űrlapon előre kiválasztott téma', 'options' => [
                        'training-online' => 'Online tréning kérése', 'training-onsite' => 'Helyszíni tréning kérése', 'general' => 'Általános kérdés',
                    ]],
                ]],

                ['_h', 'heading', 'Záró felhívás (alul)'],
                ['cta_title', 'text', 'Cím'],
                ['cta_text', 'textarea', 'Szöveg', 'rows' => 2],
            ],
        ],
        'contact' => [
            'title' => 'Kapcsolat oldal',
            'intro' => 'A kapcsolat oldal szövegei. Az e-mail címet és a helyszínt a „Beállítások” menüben módosíthatod.',
            'root'  => 'contact',
            'view'  => 'contact.php',
            'fields' => [
                ['title', 'text', 'Főcím'],
                ['intro', 'textarea', 'Bevezető', 'rows' => 3],
                ['next_steps', 'textarea', '„What happens next” lépések', 'rows' => 4, 'hint' => HINT_LINES],
            ],
        ],
        'settings' => [
            'title' => 'Beállítások',
            'intro' => 'Elérhetőségek és az üzenetek kezelése.',
            'root'  => 'settings',
            'view'  => 'contact.php',
            'fields' => [
                ['email', 'email', 'Nyilvános e-mail cím', 'hint' => 'Ez jelenik meg az oldalon, és erről a címről mennek ki az értesítők. Csak a saját domain címe lehet (…@8dnavigator.com).'],
                ['notify_email', 'email', 'Értesítés új üzenetről ide', 'hint' => 'Erre a címre kapsz levelet minden új űrlapüzenetről.'],
                ['location', 'text', 'Helyszín'],
                ['languages', 'text', 'Nyelvek'],
                ['tagline', 'text', 'Szlogen (a logó alatt)'],
                ['message_retention_months', 'number', 'Űrlapüzenetek automatikus törlése ennyi hónap után', 'min' => 1, 'max' => 120, 'hint' => 'Az adatvédelmi tájékoztató automatikusan ezt az értéket mutatja.'],
            ],
        ],
        'company' => [
            'title' => 'Cégadatok (GDPR)',
            'intro' => 'Az adatvédelmi tájékoztatóban megjelenő adatok. Amíg egy mező üres, a tájékoztatóban sárga jelölés látszik a helyén.',
            'root'  => 'company',
            'view'  => 'privacy.php',
            'fields' => [
                ['legal_name', 'text', 'Cégnév (pl. Minta Kft.)'],
                ['address', 'text', 'Székhely', 'hint' => 'Pl. 1111 Budapest, Minta utca 1., Hungary'],
                ['reg_no', 'text', 'Cégjegyzékszám'],
                ['tax_no', 'text', 'Adószám'],
                ['hosting', 'text', 'Tárhelyszolgáltató', 'hint' => 'Név, székhely, weboldal. Pl. Nethely Kft., Budapest, nethely.hu'],
                ['log_retention', 'text', 'Szervernaplók megőrzési ideje (angolul, pl. 30 days)'],
                ['privacy_effective', 'text', 'Tájékoztató hatálybalépése (angolul, pl. 1 November 2026)'],
            ],
        ],
    ];
}
