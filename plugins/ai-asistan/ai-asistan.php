
<?php
/**
 * Plugin Name: FloriBook AI
 * Description: FloriBook için AI asistan
 * Version: 1.1
 * Author: Selin Celaleddin
 */

defined('ABSPATH') || exit;

define('GROQ_API_KEY','XXXX');

add_action('wp_ajax_floribook_ai', 'floribook_ai_ajax');
add_action('wp_ajax_nopriv_floribook_ai', 'floribook_ai_ajax');

function floribook_ai_ajax() {
    check_ajax_referer('floribook_ai_nonce', 'nonce');

    $mesaj  = sanitize_text_field($_POST['user_input']);
    $gecmis = isset($_POST['gecmis']) ? json_decode(stripslashes($_POST['gecmis']), true) : [];

    if (count($gecmis) > 10) {
        array_shift($gecmis);
    }

    $gecmis[] = ['role' => 'user', 'content' => $mesaj];

    global $wpdb;
    $urunler = $wpdb->get_results("
        SELECT p.post_title AS isim, pm.min_price AS fiyat,
           pm.stock_quantity AS stok, p.ID AS id
    FROM {$wpdb->posts} p
    JOIN {$wpdb->prefix}wc_product_meta_lookup pm ON p.ID = pm.product_id
    WHERE p.post_type = 'product' AND p.post_status = 'publish'
    LIMIT 50
");

// Her ürüne gerçek URL ekle
foreach ($urunler as $urun) {
    $urun->url = get_permalink($urun->id);
}
    

    $urun_listesi = json_encode($urunler, JSON_UNESCAPED_UNICODE);

    $sistem_mesaji = "FloriBook adlı Türk kitap ve e-kitap sitesinin yapay zeka asistanısın.
    Sadece site verileri hakkında yardımcı ol.
    Mevcut ürünler: " . $urun_listesi . "
    Türkçe konuş, doğal ve akıcı cevaplar ver.
    Bir bilgiyi bilmiyorsan uydurma.
    Sitenin dışındaki konularda 'Ben sadece FloriBook hakkında yardımcı olabilirim' de.
    Bilmediğin sorularda iletişim sayfasına yönlendir: " . home_url('/iletisim') . "
    Kullanıcı ürün sorarsa ürünün url linkini kullan.
    Hassas bilgileri asla paylaşma.";

    $response = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', [
        'timeout' => 30,
        'headers' => [
            'Authorization' => 'Bearer ' . GROQ_API_KEY,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode([
            'model'    => 'llama-3.3-70b-versatile',
            'messages' => array_merge(
                [['role' => 'system', 'content' => $sistem_mesaji]],
                $gecmis
            )
        ])
    ]);

    $body  = json_decode(wp_remote_retrieve_body($response), true);
    $cevap = $body['choices'][0]['message']['content'] ?? 'Bir hata oluştu.';

    $gecmis[] = ['role' => 'assistant', 'content' => $cevap];

    wp_send_json_success([
        'cevap'  => $cevap,
        'gecmis' => $gecmis,
    ]);
}

function ai_asistan_mesaj() {
    $img      = plugin_dir_url(__FILE__) . 'ai-img.png';
    $ajax_url = admin_url('admin-ajax.php');
    $nonce    = wp_create_nonce('floribook_ai_nonce');
    ?>
    <div class="floribook-ai-wrapper">
        <div class="chat-container">
            <div class="chat-header">
                <div class="dot"></div>
                <span>FloriBook AI Asistan</span>
                <small>Çevrimiçi</small>
            </div>
            <div class="messages" id="chat-kutusu"></div>
            <div class="input-area">
                <input id="user-input" type="text" placeholder="Bir şeyler sorun...">
                <button class="send-btn" onclick="mesajGonder()">›</button>
            </div>
        </div>
    </div>

    <script>
    var floribookGecmis  = [];
    var floribookAjaxUrl = '<?php echo esc_url($ajax_url); ?>';
    var floribookNonce   = '<?php echo $nonce; ?>';
    var floribookImg     = '<?php echo esc_url($img); ?>';

    function mesajGonder() {
        var input = document.getElementById('user-input');
        var mesaj = input.value.trim();
        if (!mesaj) return;

        var kutu = document.getElementById('chat-kutusu');
        kutu.innerHTML += '<div class="message user"><div class="avatar">👤</div><div class="bubble">' + mesaj + '</div></div>';
        input.value = '';

        kutu.innerHTML += '<div class="message bot" id="yazıyor"><div class="avatar"><img src="' + floribookImg + '" alt="AI"></div><div class="bubble typing"><span></span><span></span><span></span></div></div>';
        kutu.scrollTop = kutu.scrollHeight;

        var formData = new FormData();
        formData.append('action', 'floribook_ai');
        formData.append('nonce', floribookNonce);
        formData.append('user_input', mesaj);
        formData.append('gecmis', JSON.stringify(floribookGecmis));

        fetch(floribookAjaxUrl, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            document.getElementById('yazıyor')?.remove();
            if (data.success) {
                floribookGecmis = data.data.gecmis;
                kutu.innerHTML += '<div class="message bot"><div class="avatar"><img src="' + floribookImg + '" alt="AI"></div><div class="bubble">' + data.data.cevap + '</div></div>';
            }
            kutu.scrollTop = kutu.scrollHeight;
        });
    }

    document.getElementById('user-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') mesajGonder();
    });
    </script>
    <?php
}
add_shortcode('floribook_ai', 'ai_asistan_mesaj');

function floribook_ai_style() {
    wp_enqueue_style('floribook-ai', plugin_dir_url(__FILE__) . 'style.css');
}
add_action('wp_enqueue_scripts', 'floribook_ai_style');