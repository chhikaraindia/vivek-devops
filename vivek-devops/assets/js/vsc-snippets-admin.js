/**
 * VSC Code Snippets Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize CodeMirror editor
        initCodeMirror();

        // Toggle snippet status
        initToggleSnippet();

        // Execute snippet on demand
        initExecuteSnippet();
    });

    /**
     * Initialize CodeMirror editor
     */
    function initCodeMirror() {
        var $editor = $('#vsc_snippet_code');

        if ($editor.length === 0) {
            return;
        }

        // CodeMirror is already initialized via wp_enqueue_code_editor
        // Just add some custom enhancements if needed

        // Add fullscreen toggle button
        if (typeof CodeMirror !== 'undefined' && $editor.next('.CodeMirror').length) {
            var cm = $editor.next('.CodeMirror')[0].CodeMirror;

            // Set initial height
            cm.setSize(null, 500);

            // Add keyboard shortcuts
            cm.setOption('extraKeys', {
                'Ctrl-S': function(cm) {
                    // Save snippet (submit form)
                    $('#publish, #save-post').click();
                },
                'Cmd-S': function(cm) {
                    // Save snippet (Mac)
                    $('#publish, #save-post').click();
                },
                'F11': function(cm) {
                    cm.setOption('fullScreen', !cm.getOption('fullScreen'));
                },
                'Esc': function(cm) {
                    if (cm.getOption('fullScreen')) {
                        cm.setOption('fullScreen', false);
                    }
                }
            });
        }
    }

    /**
     * Initialize toggle snippet functionality
     */
    function initToggleSnippet() {
        $(document).on('click', '.vsc-toggle-snippet', function(e) {
            e.preventDefault();

            var $button = $(this);
            var snippetId = $button.data('snippet-id');

            $button.prop('disabled', true).text('Processing...');

            $.ajax({
                url: vscSnippets.ajaxurl,
                type: 'POST',
                data: {
                    action: 'vsc_toggle_snippet',
                    nonce: vscSnippets.nonce,
                    snippet_id: snippetId
                },
                success: function(response) {
                    if (response.success) {
                        // Reload page to show updated status
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                        $button.prop('disabled', false).text('Toggle');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $button.prop('disabled', false).text('Toggle');
                }
            });
        });
    }

    /**
     * Initialize execute snippet functionality
     */
    function initExecuteSnippet() {
        $(document).on('click', '.vsc-execute-snippet', function(e) {
            e.preventDefault();

            var $button = $(this);
            var snippetId = $button.data('snippet-id');

            $button.prop('disabled', true).text('Executing...');

            $.ajax({
                url: vscSnippets.ajaxurl,
                type: 'POST',
                data: {
                    action: 'vsc_execute_snippet',
                    nonce: vscSnippets.nonce,
                    snippet_id: snippetId
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        if (response.data.output) {
                            console.log('Snippet Output:', response.data.output);
                        }
                    } else {
                        alert('Error: ' + response.data);
                    }
                    $button.prop('disabled', false).text('Execute');
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $button.prop('disabled', false).text('Execute');
                }
            });
        });
    }

    /**
     * Confirm before deleting snippet
     */
    $(document).on('click', '.submitdelete', function(e) {
        if (!confirm('Are you sure you want to delete this snippet? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });

    /**
     * Auto-save warning
     */
    var codeChanged = false;

    $('#vsc_snippet_code').on('change', function() {
        codeChanged = true;
    });

    $(window).on('beforeunload', function() {
        if (codeChanged && !$('#publish').is(':disabled')) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });

    // Clear warning after save
    $('#post').on('submit', function() {
        codeChanged = false;
    });

})(jQuery);
