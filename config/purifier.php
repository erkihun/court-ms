<?php

// config/purifier.php

return [
    'encoding'      => 'UTF-8',
    'finalize'      => true,

    // Prefer an env override, fall back to storage, and finally /tmp if storage isn't writable.
+    'cachePath'     => (function () {
        $candidate = env('PURIFIER_CACHE_PATH', storage_path('framework/cache/purifier'));

        if (!is_dir($candidate)) {
            @mkdir($candidate, 0755, true);
        }

        if (is_dir($candidate) && is_writable($candidate)) {
            return $candidate;
        }

        $tempPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'purifier-cache';
        if (!is_dir($tempPath)) {
            @mkdir($tempPath, 0755, true);
        }

        return $tempPath;
    })(),
    // File mode for cache files (directories are handled by the package)
    'cacheFileMode' => 0644,

    'settings' => [
        /**
         * Global default profile (used by Purifier::clean($html))
         * Broader allowlist for trusted content.
         */
        'default' => [
            'HTML.Doctype'             => 'XHTML 1.0 Transitional',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => false,

            // Generous allowlist (includes <img>, tables, etc.)
            'HTML.Allowed' => implode(',', [
                // inline & text
                'p[style|class|dir]',
                'br',
                'span[style|class|dir]',
                'mark[style|class]',
                's',
                'sub',
                'sup',
                'strong',
                'b',
                'em',
                'i',
                'u',
                'small',
                'code',
                'kbd',
                'samp',
                // links
                'a[href|title|target|rel|class|style]',
                // separators
                'hr',
                // lists
                'ul[style|class]',
                'ol[style|class]',
                'li[style|class]',
                // headings
                'h1[style|class]',
                'h2[style|class]',
                'h3[style|class]',
                'h4[style|class]',
                'h5[style|class]',
                'h6[style|class]',
                // blocks
                'div[style|class|dir]',
                'blockquote[style|class|cite]',
                // tables
                'table[style|class|border|cellpadding|cellspacing|width]',
                'thead[style|class]',
                'tbody[style|class]',
                'tr[style|class]',
                'th[style|class|colspan|rowspan|scope|width|height]',
                'td[style|class|colspan|rowspan|width|height]',
                // preformatted
                'pre[style|class]',
                // images
                'img[src|alt|title|width|height|style|class]',
            ]),

            'CSS.AllowedProperties' => implode(',', [
                'color',
                'background-color',
                'font-family',
                'font-size',
                'font-weight',
                'font-style',
                'text-decoration',
                'text-align',
                'line-height',
                'letter-spacing',
                'text-indent',
                'white-space',
                'direction',
                'margin',
                'margin-left',
                'margin-right',
                'margin-top',
                'margin-bottom',
                'padding',
                'padding-left',
                'padding-right',
                'padding-top',
                'padding-bottom',
                'border',
                'border-left',
                'border-right',
                'border-top',
                'border-bottom',
                'border-collapse',
                'border-spacing',
                'vertical-align',
                'width',
                'height',
                'max-width',
                'min-width',
                'list-style-type',
                'list-style-position',
            ]),

            // Allow IDs for trusted content only
            'Attr.EnableID' => true,

            // Safer link targets + limit schemes
            'Attr.AllowedFrameTargets' => ['_blank'],
            'URI.AllowedSchemes'       => ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true],

            // Ensure definition changes take effect
            'HTML.DefinitionID'  => 'global-defs',
            'HTML.DefinitionRev' => 2,
        ],

        /**
         * Strict profile for applicant/user-submitted case fields.
         * Use like: Purifier::clean($html, 'cases')
         */
        'cases' => [
            'HTML.DefinitionID'  => 'cases-def',
            'HTML.DefinitionRev' => 5, // bump when you change this profile
            'HTML.Doctype'       => 'HTML 4.01 Transitional',
            // Tight allowlist (no images/tables; minimal attributes)
            'HTML.Allowed' => implode(',', [
                // blocks
                'p',
                'br',
                'div[style|dir]',
                'blockquote',
                'ul',
                'ol[style|class]',
                'ol',
                'li',
                'hr',
                // headings
                'h1',
                'h2',
                'h3',
                'h4',
                'h5',
                'h6',
                // inline
                'b',
                'strong',
                'i',
                'em',
                'u',
                'sub',
                'sup',
                'small',
                'span[style|dir]',
                'mark',
                // links (no rel/class/style to keep it simple)
                'a[href|title|target]',
            ]),

            'CSS.AllowedProperties' => implode(',', [
                'text-align',
                'color',
                'background-color',
                'font-size',
                'font-weight',
                'font-style',
                'text-decoration',
                'line-height',
                'direction',
            ]),

            // Disallow IDs in user HTML
            'Attr.EnableID'            => false,
            'Attr.AllowedFrameTargets' => ['_blank'],

            // Only safe URI schemes
            'URI.AllowedSchemes'       => ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true],

            // Clean out empty tags
            'AutoFormat.RemoveEmpty'   => true,
            'AutoFormat.AutoParagraph' => false,
        ],

        /**
         * Extra elements/attributes appended safely to all profiles.
         * Includes <mark> so both profiles recognize it.
         */
        'custom_definition' => [
            'id'    => 'html5-definitions',
            'rev'   => 1,
            'debug' => false,
            'elements' => [
                ['mark', 'Inline', 'Inline', 'Common'],
            ],
            'attributes' => [
                ['a', 'target', 'Enum#_blank,_self,_parent,_top'],
            ],
        ],
    ],
];
