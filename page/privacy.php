<?php
/**
 * 개인정보처리방침 (샘플)
 * - 실제 운영 전 법무·개인정보 담당자 검토 필수
 * - URL: /page/privacy.php
 */
include_once(dirname(__FILE__).'/_init.php');

if (!function_exists('g5site_cfg')) {
    if (is_file(G5_PATH . '/_site.config.php')) {
        include_once G5_PATH . '/_site.config.php';
    }
}

$privacy_site_name   = function_exists('g5site_cfg') ? g5site_cfg('site_name', '본 사이트') : '본 사이트';
$privacy_company     = function_exists('g5site_cfg') ? g5site_cfg('company_name', $privacy_site_name) : $privacy_site_name;
$privacy_ceo         = function_exists('g5site_cfg') ? g5site_cfg('ceo_name', '대표자명') : '대표자명';
$privacy_email       = function_exists('g5site_cfg') ? g5site_cfg('email', 'help@example.com') : 'help@example.com';
$privacy_phone       = function_exists('g5site_cfg') ? g5site_cfg('phone', '010-0000-0000') : '010-0000-0000';
$privacy_address     = function_exists('g5site_cfg') ? g5site_cfg('address', '주소를 입력하세요') : '주소를 입력하세요';
$privacy_manager     = function_exists('g5site_cfg') ? g5site_cfg('privacy_manager', '') : '';
if ($privacy_manager === '') {
    $privacy_manager = $privacy_ceo;
}

$page_title       = '개인정보처리방침';
$page_description = $privacy_company.'의 개인정보 수집·이용·보관 및 정보주체 권리에 관한 안내입니다.';
$page_robots      = 'index,follow';

g5_page_start('개인정보처리방침');
?>
<div class="page-template page-privacy">
    <header class="page-hero reveal">
        <div class="page-inner">
            <p class="page-eyebrow">Privacy</p>
            <h1 class="page-title">개인정보처리방침</h1>
            <p class="page-desc">
                <?php echo htmlspecialchars($privacy_company, ENT_QUOTES, 'UTF-8'); ?>(이하 &quot;회사&quot;)는
                「개인정보 보호법」 등 관련 법령을 준수하며, 이용자의 개인정보를 보호합니다.
            </p>
            <p class="page-section__desc" role="note">
                <strong>안내:</strong> 본 문서는 <strong>샘플 템플릿</strong>입니다.
                실제 서비스·수집 항목·보관 기간에 맞게 법무 검토 후 수정·시행일을 갱신해 주세요.
            </p>
        </div>
    </header>

    <article class="page-section page-privacy__body reveal">
        <div class="page-inner">
            <section class="page-privacy__block">
                <h2 class="page-section__title">1. 수집하는 개인정보 항목</h2>
                <p class="page-section__desc">회사는 다음의 개인정보를 수집할 수 있습니다.</p>
                <ul class="page-list">
                    <li><strong>상담·문의:</strong> 이름, 연락처(전화번호), 이메일, 문의내용</li>
                    <li><strong>회원 가입·이용:</strong> 아이디, 비밀번호, 이름, 연락처, 이메일 (그누보드 회원 기능 사용 시)</li>
                    <li><strong>자동 수집:</strong> IP 주소, 쿠키, 접속 로그, 기기 정보 (서비스 이용 과정에서 생성될 수 있음)</li>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">2. 개인정보 수집 및 이용 목적</h2>
                <ul class="page-list">
                    <li>상담·문의 접수 및 회신</li>
                    <li>회원 식별·인증 및 게시판 서비스 제공</li>
                    <li>서비스 개선, 부정 이용 방지, 통계·분석 (익명화 가능한 범위)</li>
                    <li>법령상 의무 이행</li>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">3. 보유 및 이용 기간</h2>
                <p>원칙적으로 목적 달성 후 지체 없이 파기합니다. 다만 관계 법령에 따라 보관이 필요한 경우 해당 기간 동안 보관합니다.</p>
                <ul class="page-list">
                    <li>상담·문의 기록: 상담 완료 후 <strong>1년</strong> (샘플 — 실제 정책에 맞게 수정)</li>
                    <li>회원 정보: 회원 탈퇴 시까지 (관련 법령에 따른 보관 예외 적용)</li>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">4. 제3자 제공</h2>
                <p>회사는 원칙적으로 이용자의 개인정보를 제3자에게 제공하지 않습니다. 다만 이용자 동의가 있거나 법령에 근거한 경우 예외적으로 제공할 수 있습니다.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">5. 처리 위탁</h2>
                <p>원활한 서비스 제공을 위해 호스팅·이메일 발송 등 일부 업무를 위탁할 수 있으며, 위탁 시 수탁자·위탁 업무 내용을 본 방침 또는 별도 공지로 안내합니다. (실제 위탁 업체가 있으면 명시해 주세요.)</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">6. 정보주체의 권리</h2>
                <p>이용자는 언제든지 다음 권리를 행사할 수 있습니다.</p>
                <ul class="page-list">
                    <li>개인정보 열람·정정·삭제·처리정지 요구</li>
                    <li>동의 철회 (동의를 통해 수집한 경우)</li>
                </ul>
                <p>요청은 아래 개인정보 보호책임자에게 서면·이메일·전화로 할 수 있습니다.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">7. 개인정보 보호책임자</h2>
                <ul class="page-list">
                    <li><strong>회사명:</strong> <?php echo htmlspecialchars($privacy_company, ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><strong>개인정보 보호책임자:</strong> <?php echo htmlspecialchars($privacy_manager, ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><strong>연락처:</strong> <?php echo htmlspecialchars($privacy_phone, ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><strong>이메일:</strong> <a href="mailto:<?php echo htmlspecialchars($privacy_email, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($privacy_email, ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <li><strong>주소:</strong> <?php echo htmlspecialchars($privacy_address, ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">8. 쿠키 사용 안내</h2>
                <p>회사는 이용자에게 맞춤 서비스를 제공하기 위해 쿠키를 사용할 수 있습니다. 브라우저 설정에서 쿠키 저장을 거부할 수 있으나, 일부 서비스 이용에 제한이 있을 수 있습니다.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">9. 방침 변경</h2>
                <p>본 개인정보처리방침이 변경되는 경우 웹사이트 공지 또는 본 페이지를 통해 안내합니다.</p>
            </section>

            <section class="page-privacy__block">
                <h2 class="page-section__title">10. 시행일</h2>
                <p>본 방침은 <strong>2026년 5월 21일</strong>부터 시행합니다. (실제 시행일로 수정해 주세요.)</p>
            </section>
        </div>
    </article>
</div>
<?php
g5_page_end();
