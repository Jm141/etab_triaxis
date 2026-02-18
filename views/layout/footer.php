    <?php if (Session::has('user_id')): ?>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <strong>Copyright &copy; <?= date('Y') ?> <a href="https://triconnect-9xmz.onrender.com/" target="_blank">TriAccess Group</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0.0
            </div>
        </footer>
    </div>
    <?php endif; ?>

    <!-- jQuery -->
    <script src="/tabulation/public/assets/js/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="/tabulation/public/assets/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="/tabulation/public/assets/js/sweetalert2.min.js"></script>
    <!-- AdminLTE App -->
    <script src="/tabulation/public/assets/js/adminlte.min.js"></script>
    
    <!-- SweetAlert Helper Functions -->
    <script>
        // Helper function for delete confirmations
        function confirmDelete(event, message, title = 'Are you sure?') {
            event.preventDefault();
            const form = event.target.closest('form');
            
            Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
        
        // Helper function for general confirmations
        function confirmAction(event, message, title = 'Confirm', confirmText = 'Yes, do it!') {
            event.preventDefault();
            const form = event.target.closest('form');
            const callback = event.target.getAttribute('data-callback');
            
            Swal.fire({
                title: title,
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (callback && typeof window[callback] === 'function') {
                        window[callback]();
                    } else if (form) {
                        form.submit();
                    }
                }
            });
        }
        
        // Helper function to copy text to clipboard
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

    <?php if (Session::has('user_id') && Session::get('role_name') === 'Judge'): ?>
    <script>
    // Judge heartbeat (ping every 3 seconds)
    (function() {
        if (typeof $ === 'undefined') return;
        const csrfToken = <?php echo json_encode(Session::getCSRFToken() ?: '', JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        function sendHeartbeat() {
            $.ajax({
                url: '/tabulation/api/judge-heartbeat',
                method: 'POST',
                dataType: 'json',
                data: { csrf_token: csrfToken }
            });
        }
        // Send immediately and then every 3 seconds
        $(document).ready(function() {
            sendHeartbeat();
            setInterval(sendHeartbeat, 3000);
        });
    })();
    </script>
    <?php endif; ?>
</body>
</html>
