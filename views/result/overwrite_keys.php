<?php 
$title = 'Generate Overwrite Keys - ' . $event['name'];
require __DIR__ . '/../layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-key"></i> Generate Overwrite Keys</h1>
    <a href="/tabulation/events/<?= $event['id'] ?>/results" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Results
    </a>
</div>

<div class="alert alert-info">
    <h5><i class="icon fas fa-info-circle"></i> Overwrite Keys</h5>
    <p class="mb-0">
        <strong>What are Overwrite Keys?</strong> These keys allow Technical Admins to edit scores without judge permission.
        Generate a key for a specific score, then share it with the Technical Admin who needs to edit that score.
        The key can only be used once per score edit.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list"></i> Submitted Scores</h5>
    </div>
    <div class="card-body">
        <?php if (empty($scores)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                No submitted scores found for this event.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Round</th>
                            <th>Contestant</th>
                            <th>Judge</th>
                            <th>Judge Permission</th>
                            <th>Overwrite Key</th>
                            <th>Key Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scores as $score): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($score['round_name']) ?></strong></td>
                            <td>
                                #<?= htmlspecialchars($score['contestant_number']) ?> - 
                                <?= htmlspecialchars($score['contestant_name']) ?>
                            </td>
                            <td>
                                Judge #<?= htmlspecialchars($score['judge_number']) ?> - 
                                <?= htmlspecialchars($score['judge_name']) ?>
                            </td>
                            <td>
                                <?php if ($score['admin_edit_allowed']): ?>
                                    <span class="badge bg-success">Granted</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Not Granted</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($score['overwrite_key'])): ?>
                                    <code id="key-<?= $score['score_id'] ?>" class="text-primary">
                                        <?= htmlspecialchars($score['overwrite_key']) ?>
                                    </code>
                                    <button class="btn btn-sm btn-outline-secondary" 
                                            onclick="copyToClipboard('<?= htmlspecialchars($score['overwrite_key']) ?>', 'key-<?= $score['score_id'] ?>')"
                                            title="Copy key">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">Not generated</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($score['overwrite_key'])): ?>
                                    <?php if (!empty($score['overwrite_key_set_at'])): ?>
                                        <small class="text-muted">
                                            Generated: <?= date('M d, Y H:i', strtotime($score['overwrite_key_set_at'])) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (empty($score['overwrite_key'])): ?>
                                    <button class="btn btn-sm btn-primary generate-key-btn" 
                                            data-score-id="<?= $score['score_id'] ?>"
                                            data-contestant="<?= htmlspecialchars($score['contestant_name']) ?>"
                                            data-judge="<?= htmlspecialchars($score['judge_name']) ?>">
                                        <i class="fas fa-key"></i> Generate Key
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-warning regenerate-key-btn" 
                                            data-score-id="<?= $score['score_id'] ?>"
                                            data-contestant="<?= htmlspecialchars($score['contestant_name']) ?>"
                                            data-judge="<?= htmlspecialchars($score['judge_name']) ?>">
                                        <i class="fas fa-sync"></i> Regenerate
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    // Generate overwrite key
    $('.generate-key-btn, .regenerate-key-btn').on('click', function() {
        const scoreId = $(this).data('score-id');
        const contestant = $(this).data('contestant');
        const judge = $(this).data('judge');
        const isRegenerate = $(this).hasClass('regenerate-key-btn');
        
        Swal.fire({
            title: isRegenerate ? 'Regenerate Overwrite Key?' : 'Generate Overwrite Key?',
            html: `Generate overwrite key for:<br>
                   <strong>Contestant:</strong> ${contestant}<br>
                   <strong>Judge:</strong> ${judge}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: isRegenerate ? 'Yes, regenerate' : 'Yes, generate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Generating...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Make API call
                $.ajax({
                    url: '/tabulation/score-management/' + scoreId + '/generate-overwrite-key',
                    method: 'POST',
                    data: {
                        csrf_token: '<?= Session::getCSRFToken() ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                html: `Overwrite key generated:<br>
                                       <code style="font-size: 1.2em; padding: 10px; background: #f0f0f0; display: block; margin: 10px 0;">${response.overwrite_key}</code>
                                       <button class="btn btn-primary" onclick="copyToClipboard('${response.overwrite_key}', 'swal-key')">Copy Key</button>`,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Failed to generate key',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to generate overwrite key. Please try again.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
});

function copyToClipboard(text, elementId) {
    navigator.clipboard.writeText(text).then(function() {
        const element = document.getElementById(elementId);
        if (element) {
            const originalText = element.textContent;
            element.textContent = 'Copied!';
            element.style.color = '#28a745';
            setTimeout(function() {
                element.textContent = originalText;
                element.style.color = '';
            }, 2000);
        } else {
            Swal.fire({
                title: 'Copied!',
                text: 'Key copied to clipboard',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }).catch(function(err) {
        console.error('Failed to copy: ', err);
        Swal.fire({
            title: 'Error',
            text: 'Failed to copy to clipboard',
            icon: 'error'
        });
    });
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
