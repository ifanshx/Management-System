<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 20px; }
    .page-title h1 { font-size: 24px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; }

    /* CHAT APP LAYOUT */
    .chat-container { display: flex; height: 75vh; min-height: 500px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-card); }
    
    /* LEFT SIDE: INBOX LIST */
    .chat-sidebar { width: 320px; background: var(--bg-base); border-right: 1px solid var(--border-subtle); display: flex; flex-direction: column; }
    .sidebar-header { padding: 20px; border-bottom: 1px solid var(--border-subtle); font-weight: 800; font-size: 15px; color: var(--text-main); }
    
    .chat-list { flex: 1; overflow-y: auto; }
    .chat-item { padding: 15px 20px; border-bottom: 1px solid var(--border-subtle); cursor: pointer; transition: 0.2s; display: flex; gap: 12px; align-items: center; }
    .chat-item:hover { background: rgba(0,0,0,0.02); }
    html.dark .chat-item:hover { background: rgba(255,255,255,0.02); }
    
    .avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--accent-light); color: var(--accent-main); display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }
    
    .chat-preview { flex: 1; overflow: hidden; }
    .chat-name { font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; display: flex; justify-content: space-between;}
    .chat-time { font-size: 10px; color: var(--text-muted); font-weight: 500; }
    .chat-snippet { font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* RIGHT SIDE: CHAT ROOM (Empty State) */
    .chat-room { flex: 1; display: flex; flex-direction: column; background: var(--bg-surface); }
    .chat-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--text-muted); }
    .chat-empty i { font-size: 64px; color: var(--border-subtle); margin-bottom: 15px; }

</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-chats" style="color: #f97316;"></i> Customer Service Hub</h1>
        <p>Kelola semua pesan pelanggan dari toko <b><?= esc($shop['shop_name']) ?></b> tanpa harus buka Shopee.</p>
    </div>
</div>

<div class="chat-container">
    
    <div class="chat-sidebar">
        <div class="sidebar-header">
            Pesan Masuk Terbaru
        </div>
        <div class="chat-list">
            <?php if(empty($chats)): ?>
                <div style="padding: 30px 20px; text-align: center; color: var(--text-muted); font-size: 13px;">Tidak ada pesan masuk.</div>
            <?php else: ?>
                <?php foreach($chats as $chat): ?>
                    <?php 
                        $buyerName = $chat['to_name'] ?? 'Pelanggan'; 
                        $buyerId = $chat['to_id'];
                        // Ambil snippet pesan terakhir
                        $snippet = $chat['latest_message_content']['text'] ?? 'Menerima Media/Gambar';
                        $time = date('H:i', $chat['last_message_timestamp']);
                    ?>
                    <div class="chat-item" onclick="openChat(<?= $buyerId ?>, '<?= esc($buyerName) ?>')">
                        <div class="avatar"><?= substr($buyerName, 0, 1) ?></div>
                        <div class="chat-preview">
                            <div class="chat-name">
                                <span><?= esc($buyerName) ?></span>
                                <span class="chat-time"><?= $time ?></span>
                            </div>
                            <div class="chat-snippet"><?= esc($snippet) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="chat-room" id="chatRoomBox">
        <div class="chat-empty">
            <i class="ph ph-chat-circle-dots"></i>
            <h3 style="font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 5px;">Pilih Obrolan</h3>
            <p style="font-size: 13px;">Pilih salah satu pesan di samping kiri untuk mulai membalas.</p>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Fitur Quick Reply Sederhana
    function openChat(buyerId, buyerName) {
        Swal.fire({
            title: `Balas ke ${buyerName}`,
            input: 'textarea',
            inputPlaceholder: 'Ketik balasan Anda di sini...',
            inputAttributes: {
                'aria-label': 'Ketik balasan Anda di sini'
            },
            showCancelButton: true,
            confirmButtonText: '<i class="ph ph-paper-plane-right"></i> Kirim Pesan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#f97316',
            showLoaderOnConfirm: true,
            preConfirm: (text) => {
                if (!text) {
                    Swal.showValidationMessage('Pesan tidak boleh kosong');
                    return false;
                }

                let formData = new FormData();
                formData.append('shop_id', '<?= $shop['shop_id'] ?>');
                formData.append('to_id', buyerId);
                formData.append('message', text);
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                return fetch('<?= base_url('/customerservice/reply_chat') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error(response.statusText)
                    return response.json()
                })
                .catch(error => {
                    Swal.showValidationMessage(`Gagal mengirim: ${error}`);
                })
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if(result.value.success) {
                    Swal.fire('Terkirim!', 'Pesan Anda berhasil dikirim ke aplikasi Shopee pembeli.', 'success');
                } else {
                    Swal.fire('Gagal', result.value.message, 'error');
                }
            }
        });
    }
</script>

<?= $this->endSection() ?>