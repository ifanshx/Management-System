<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .review-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); gap: 20px; }
    @media (max-width: 768px) { .review-grid { grid-template-columns: 1fr; } }

    .review-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 20px; box-shadow: var(--shadow-card); display: flex; flex-direction: column;}
    
    .r-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;}
    .buyer-info { display: flex; align-items: center; gap: 10px; }
    .buyer-avatar { width: 36px; height: 36px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800;}
    .buyer-name { font-size: 14px; font-weight: 800; color: var(--text-main);}
    .r-date { font-size: 11px; color: var(--text-muted); font-family: monospace;}

    .stars { color: #f59e0b; font-size: 14px; display: flex; gap: 2px;}
    .stars.bad { color: #ef4444; }

    .product-info { background: var(--bg-base); padding: 10px; border-radius: 8px; border: 1px solid var(--border-subtle); font-size: 11px; color: var(--text-muted); margin-bottom: 15px; display: flex; gap: 10px; align-items: center;}
    
    .r-text { font-size: 13px; color: var(--text-main); line-height: 1.5; margin-bottom: 15px; flex-grow: 1;}

    .r-reply-box { background: rgba(16, 185, 129, 0.05); border-left: 3px solid #10b981; padding: 12px 15px; border-radius: 0 8px 8px 0; margin-bottom: 15px; font-size: 12px; color: var(--text-main);}
    .r-reply-title { font-weight: 800; color: #10b981; margin-bottom: 5px; font-size: 11px; text-transform: uppercase;}

    .reply-form { display: flex; gap: 10px; margin-top: auto;}
    .reply-input { flex: 1; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 10px 15px; border-radius: 8px; font-size: 12px; outline: none; resize: none; font-family: inherit; color: var(--text-main);}
    .reply-input:focus { border-color: #3b82f6;}
    
    .btn-send { background: #3b82f6; color: #fff; border: none; padding: 0 15px; border-radius: 8px; font-weight: 800; cursor: pointer; transition: 0.2s;}
    .btn-send:hover { background: #2563eb; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-star" style="color: #f59e0b;"></i> Manajemen Reputasi & Ulasan</h1>
        <p>Balas ulasan pembeli toko <b><?= esc($shop['shop_name']) ?></b> untuk menjaga performa algoritma pencarian.</p>
    </div>
</div>

<?php if(empty($reviews)): ?>
    <div style="text-align: center; padding: 50px; background: var(--bg-surface); border-radius: 16px; border: 1px dashed var(--border-subtle);">
        <i class="ph ph-chat-circle-text" style="font-size: 48px; color: var(--text-muted); margin-bottom: 10px;"></i>
        <div style="font-weight: 800; color: var(--text-main);">Belum Ada Ulasan</div>
    </div>
<?php else: ?>
    <div class="review-grid">
        <?php foreach($reviews as $r): ?>
            <div class="review-card">
                <div class="r-header">
                    <div class="buyer-info">
                        <div class="buyer-avatar"><i class="ph ph-user"></i></div>
                        <div>
                            <div class="buyer-name"><?= esc($r['buyer_username'] ?? 'Pembeli') ?></div>
                            <div class="stars <?= ($r['rating_star'] <= 3) ? 'bad' : '' ?>">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <?= ($i <= $r['rating_star']) ? '<i class="ph-fill ph-star"></i>' : '<i class="ph ph-star"></i>' ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <div class="r-date"><?= date('d M Y, H:i', $r['create_time']) ?></div>
                </div>

                <div class="r-text">
                    "<?= esc($r['comment']) ?: '<i style="color: var(--text-muted);">Pembeli tidak meninggalkan pesan teks.</i>' ?>"
                </div>

                <?php if(!empty($r['reply'])): ?>
                    <div class="r-reply-box" id="reply_box_<?= $r['comment_id'] ?>">
                        <div class="r-reply-title">Balasan Anda:</div>
                        <div id="reply_text_<?= $r['comment_id'] ?>"><?= nl2br(esc($r['reply'])) ?></div>
                    </div>
                <?php else: ?>
                    <div class="r-reply-box" id="reply_box_<?= $r['comment_id'] ?>" style="display:none;">
                        <div class="r-reply-title">Balasan Anda:</div>
                        <div id="reply_text_<?= $r['comment_id'] ?>"></div>
                    </div>
                <?php endif; ?>

                <div class="reply-form">
                    <textarea id="input_<?= $r['comment_id'] ?>" class="reply-input" rows="2" placeholder="Ketik balasan profesional Anda di sini..."></textarea>
                    <button class="btn-send" onclick="sendReply('<?= $r['comment_id'] ?>')"><i class="ph ph-paper-plane-right"></i></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function sendReply(commentId) {
        let textInput = document.getElementById('input_' + commentId);
        let replyText = textInput.value.trim();

        if(!replyText) {
            Swal.fire('Kosong', 'Silakan ketik balasan terlebih dahulu.', 'warning');
            return;
        }

        let formData = new FormData();
        formData.append('comment_id', commentId);
        formData.append('reply_text', replyText);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        let btn = textInput.nextElementSibling;
        let originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="ph ph-spinner ph-spin"></i>';
        btn.disabled = true;

        fetch('<?= base_url('/shopee/reply_review/'.$shop['shop_id']) ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalIcon;
            btn.disabled = false;

            if(data.success) {
                // Tampilkan hasil balasan secara real-time tanpa reload
                document.getElementById('reply_text_' + commentId).innerText = replyText;
                document.getElementById('reply_box_' + commentId).style.display = 'block';
                textInput.value = ''; // Bersihkan input

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Balasan terkirim!',
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(error => {
            btn.innerHTML = originalIcon;
            btn.disabled = false;
            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        });
    }
</script>

<?= $this->endSection() ?>