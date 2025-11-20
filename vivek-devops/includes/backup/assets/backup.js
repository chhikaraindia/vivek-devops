jQuery(document).ready(function($) {
    // Create backup
    $('#vsc-create-backup').on('click', function() {
        var $btn = $(this);
        var $progress = $('#vsc-backup-progress');

        $btn.prop('disabled', true);
        $progress.show();

        $.ajax({
            url: vscBackup.ajax_url,
            type: 'POST',
            data: {
                action: 'vsc_create_backup',
                nonce: vscBackup.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Backup created successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('Failed to create backup');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $progress.hide();
            }
        });
    });

    // Restore backup
    $(document).on('click', '.vsc-restore-backup', function() {
        if (!confirm('Are you sure you want to restore this backup? This will overwrite your current site.')) {
            return;
        }

        var backupId = $(this).data('id');
        var $btn = $(this);

        $btn.prop('disabled', true).text('Restoring...');

        $.ajax({
            url: vscBackup.ajax_url,
            type: 'POST',
            data: {
                action: 'vsc_restore_backup',
                nonce: vscBackup.nonce,
                backup_id: backupId
            },
            success: function(response) {
                if (response.success) {
                    alert('Backup restored successfully! Page will reload.');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                    $btn.prop('disabled', false).text('Restore');
                }
            },
            error: function() {
                alert('Failed to restore backup');
                $btn.prop('disabled', false).text('Restore');
            }
        });
    });

    // Delete backup
    $(document).on('click', '.vsc-delete-backup', function() {
        if (!confirm('Are you sure you want to delete this backup?')) {
            return;
        }

        var backupId = $(this).data('id');
        var $row = $(this).closest('tr');

        $.ajax({
            url: vscBackup.ajax_url,
            type: 'POST',
            data: {
                action: 'vsc_delete_backup',
                nonce: vscBackup.nonce,
                backup_id: backupId
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('Failed to delete backup');
            }
        });
    });

    // Upload backup
    $('#vsc-upload-form').on('submit', function(e) {
        e.preventDefault();

        var fileInput = $('#vsc-backup-file')[0];
        if (!fileInput.files.length) {
            alert('Please select a backup file');
            return;
        }

        var formData = new FormData();
        formData.append('action', 'vsc_upload_backup');
        formData.append('nonce', vscBackup.nonce);
        formData.append('backup_file', fileInput.files[0]);

        var $progress = $('#vsc-upload-progress');
        var $btn = $(this).find('button');

        $btn.prop('disabled', true);
        $progress.show();

        $.ajax({
            url: vscBackup.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Backup uploaded successfully! You can now restore it.');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('Failed to upload backup');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $progress.hide();
            }
        });
    });
});
