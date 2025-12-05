<?php

return [
    'title' => 'Bench Notes',

    'page_header' => [
        'index' => 'Bench notes',
        'create' => 'New bench note',
        'edit' => 'Edit bench note',
    ],

    'headings' => [
        'create' => 'Create Bench Note',
        'create_intro' => 'Fill in the details below to create a new bench note.',
        'new' => 'New Bench Note',
        'edit' => 'Edit Bench Note',
        'edit_intro' => 'Make changes to the bench note below.',
        'edit_note' => 'Edit Note: :title',
        'about' => 'About Bench Notes',
        'editing_note' => 'Editing Note',
    ],

    'descriptions' => [
        'index' => 'Internal notes tied to cases (sanitized via HTML Purifier).',
        'create' => 'Attach a note to a case. Content is sanitized via HTML Purifier.',
        'edit' => 'Update the note content. All changes are sanitized via HTML Purifier.',
        'about' => 'Bench notes are internal notes attached to cases. They support rich text formatting, and all content is automatically sanitized via HTML Purifier for security. Notes can be edited or deleted at any time by authorized users.',
        'editing_notice' => 'You are editing an existing bench note. All changes will be saved immediately when you click "Update Note".',
        'created_meta' => 'The note was originally created by :author on :date.',
    ],

    'sections' => [
        'basic_info' => 'Basic Information',
        'note_content' => 'Note Content',
    ],

    'labels' => [
        'cases' => 'Cases',
        'case' => 'Case',
        'case_prefix' => 'Case:',
        'filter_by_case' => 'Filter by case',
        'title' => 'Title',
        'note_editor' => 'Note Editor',
        'author' => 'Author',
        'created' => 'Created',
        'created_date' => 'Created Date',
        'created_time' => 'Created Time',
        'last_updated' => 'Last Updated',
    ],

    'options' => [
        'all_cases' => 'All cases',
    ],

    'placeholders' => [
        'select_case' => 'Select a case',
        'title' => 'Enter a descriptive title',
    ],

    'helpers' => [
        'select_case' => 'Select the case this note belongs to',
        'title' => 'Brief title for the note (max 255 characters)',
        'note_editor' => 'Rich text editor powered by TinyMCE. All HTML is purified before saving.',
        'empty_content' => 'No content provided.',
    ],

    'buttons' => [
        'new_note' => 'New note',
        'create_new' => 'Create New Note',
        'back' => 'Back to Notes',
        'back_list' => 'Back to list',
        'apply' => 'Apply',
        'clear' => 'Clear',
        'cancel' => 'Cancel',
        'save' => 'Save Bench Note',
        'update' => 'Update Note',
        'edit' => 'Edit Note',
        'delete' => 'Delete',
    ],

    'empty' => [
        'title' => 'No bench notes found',
        'description_case' => 'No notes found for this case. Create your first note to get started.',
        'description_general' => 'Get started by creating your first bench note.',
    ],

    'confirmations' => [
        'delete' => 'Are you sure you want to delete this note? This action cannot be undone.',
    ],

    'meta' => [
        'na' => 'N/A',
        'unknown' => 'Unknown',
    ],
];
