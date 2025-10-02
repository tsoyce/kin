<?php
// Enable dynamic features: include helper functions and determine the current user.
require_once __DIR__ . '/helpers.php';
// Fetch the current authenticated user if any.  This makes it possible
// to conditionally show login/registration links inside the Tools modal.
$u = user();

// Fetch contact details from settings.  These values can be managed
// via the admin panel.  Provide sensible defaults if settings are missing.
$contact_email   = get_setting('contact_email', 'hello@katindir.agency');
$contact_phone   = get_setting('contact_phone', '+7 999 000-00-00');
$contact_address = get_setting('contact_address', 'Тюмень, ул. Креативная, 1');
$contact_telegram= get_setting('contact_telegram','@katindirnet');

// Fetch profiles for the main cast (Катя, Индира, Артём).  We query the
// profiles table and build an associative map keyed by name.  If a profile
// is missing in the DB, fallback to static defaults.
$profiles = [];
try {
    $stmt = App\Bootstrap::$pdo->query('SELECT * FROM profiles');
    foreach ($stmt->fetchAll() as $p) {
        $profiles[$p['name']] = $p;
    }
} catch (\Throwable $e) {
    $profiles = [];
}
function prof(string $name, array $profiles, array $fallback): array {
    return $profiles[$name] ?? $fallback;
}
$fallbackKatya = [
    'name'=>'Катя','image'=>'images/1.jpg','medal'=>'💎 Crypto Queen of Jokes',
    'role'=>'Директор по листингу улыбок','joke'=>'«Вывожу мемы в кэш и обратно. Комиссия — одна смешинка.»',
    'skills'=>'SMM,Copy,Design,Pitch','about'=>'Люблю мемы, кофе и котиков. Руководитель отдела улыбок.'
];
$fallbackIndira = [
    'name'=>'Индира','image'=>'images/2.jpg','medal'=>'🐸 Meme Investor of the Year',
    'role'=>'Главная по дивидендам хихиканья','joke'=>'«Дивиденды капают звуком ха-ха. Реинвестирую до слёз.»',
    'skills'=>'Sales,PM,Brand,Humor','about'=>'Инвестор в настроение. Добываю смех при любой волатильности.'
];
$fallbackArtem = [
    'name'=>'Артём','image'=>'images/art.png','medal'=>'🎩 Meme Mastermind',
    'role'=>'Основатель','joke'=>'«Координирую котиков и крипто-мемы.»',
    'skills'=>'Strategy,UX,Brand,Motion','about'=>'Основатель Катиндирнета. Люблю ванильную эстетику и мем-стратегии.'
];
$katya = prof('Катя', $profiles, $fallbackKatya);
$indira= prof('Индира',$profiles, $fallbackIndira);
$art   = prof('Артём', $profiles, $fallbackArtem);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1, viewport-fit=cover"
  />
  <title>Катиндирнет — Креативное агентство</title>

  <!-- Цвет панелей браузера (iOS/Android) -->
  <meta name="theme-color" content="#0a0e14" />
  <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#0a0e14" />
  <meta name="theme-color" media="(prefers-color-scheme: light)" content="#f0f2f5" />

  <!-- iOS PWA статус-бар -->
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

  <meta name="description" content="Катиндирнет: котики с небес, а кто маркетинг полез?" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/tokens.css">
  <link rel="stylesheet" href="css/theme-classic-plus.css">
  <link rel="stylesheet" href="css/theme-neon-plus.css">
  <link rel="stylesheet" href="css/theme-light.css">
  <link rel="stylesheet" href="css/print.css" media="print">
  <script src="js/theme.js"></script>

  
</head>
<body>
  <div class="sky" id="sky" aria-hidden="true"></div>

  <audio id="sfx" src="music.mp3" preload="auto" loop></audio>

  <div class="wrap" id="appRoot">
    <header>
      <div class="brand">
        <div class="logo">КА</div>
        <div>
          <h1>Катиндирнет</h1>
          <div class="subtitle">Креативное агентство</div>
        </div>
      </div>

      <div class="controls">
        <button class="btn" id="openContacts">Контакты 📇</button>
        <?php
          // Build JSON for the founder (Артём) using dynamic data.  Provide
          // the photo in the data so JS can display it.  The skills string is
          // split into an array.
          $artSkillsArr = array_filter(array_map('trim', explode(',', $art['skills'] ?? '')));
          $artBio = [
              'name'  => $art['name'],
              'about' => $art['about'],
              'skills'=> $artSkillsArr,
              'photo' => $art['image']
          ];
        ?>
        <button class="btn" id="openBioMe" data-bio='<?php echo h(json_encode($artBio, JSON_UNESCAPED_UNICODE)); ?>'>BIO (<?php echo h($art['name']); ?>)</button>
        <button class="btn" id="openTools">🧰 Инструменты</button>

        <div class="toolbar">
          <div class="group" role="group" aria-label="Действия">
            <button class="tool" id="copyLink" title="Скопировать ссылку">🔗</button>
            <span class="sep" aria-hidden="true"></span>
            <button class="tool" id="shareBtn" title="Поделиться">📤</button>
            <span class="sep" aria-hidden="true"></span>
            <button class="tool" id="randCaption" title="Случайная подпись">🎲</button>
            <span class="sep" aria-hidden="true"></span>
            <button class="tool" id="exportMeme" title="Сделать мем-скрин">📸</button>
            <span class="sep" aria-hidden="true"></span>
            <button class="tool" id="openSettings" title="Настройки">⚙️</button>
          </div>
        </div>
      </div>
    </header>

    <div class="ticker" aria-live="polite"><div class="ticker-track" id="ticker"></div></div>

    <section class="hero">
      <h2 class="title">Долистались до фондового рынка смеха</h2>
      <p class="caption" id="slogan">Улыбка добавлена в корзину</p>

      <div class="grid">
        <!-- КАТЯ (динамический) -->
        <article class="card" data-purr="true">
          <label class="avatar" title="Кликни, чтобы заменить фото">
            <img id="img-katya" src="<?php echo h($katya['image']); ?>" alt="<?php echo h($katya['name']); ?>"/>
            <input type="file" id="file-katya" accept="image/*" style="display:none"/>
          </label>
          <div class="info">
            <div class="person-head">
              <h3 class="name"><?php echo h($katya['name']); ?></h3>
              <div class="badge-row"><span class="badge"><?php echo h($katya['medal']); ?></span></div>
            </div>
            <p class="role"><?php echo h($katya['role']); ?></p>
            <p class="joke"><?php echo h($katya['joke']); ?></p>
            <div class="controls compact">
              <button class="btn" onclick="toast('<?php echo h($katya['name']); ?> вывела мемы в кэш 💸')">Кэш</button>
              <button class="btn primary" onclick="confetti(); toast('IPO улыбки! 🎉')">IPO</button>
              <?php
              // Build data-bio JSON for Katya.  Split skills into array.
              $skillsArr = array_filter(array_map('trim', explode(',', $katya['skills'] ?? '')));
              $bioData = [
                  'name'  => $katya['name'],
                  'about' => $katya['about'],
                  'skills'=> $skillsArr
              ];
              ?>
              <button class="btn" data-bio='<?php echo h(json_encode($bioData, JSON_UNESCAPED_UNICODE)); ?>'>BIO</button>
            </div>
          </div>
        </article>

        <!-- ИНДИРА (динамический) -->
        <article class="card" data-purr="true">
          <label class="avatar" title="Кликни, чтобы заменить фото">
            <img id="img-indira" src="<?php echo h($indira['image']); ?>" alt="<?php echo h($indira['name']); ?>"/>
            <input type="file" id="file-indira" accept="image/*" style="display:none"/>
          </label>
          <div class="info">
            <div class="person-head">
              <h3 class="name"><?php echo h($indira['name']); ?></h3>
              <div class="badge-row"><span class="badge"><?php echo h($indira['medal']); ?></span></div>
            </div>
            <p class="role"><?php echo h($indira['role']); ?></p>
            <p class="joke"><?php echo h($indira['joke']); ?></p>
            <div class="controls compact">
              <button class="btn" onclick="toast('<?php echo h($indira['name']); ?> купила интернет за 3 смешинки 🤑')">Интернет</button>
              <button class="btn primary" onclick="confetti(); toast('+100 смешинок!')">+100</button>
              <?php
              $skillsArr2 = array_filter(array_map('trim', explode(',', $indira['skills'] ?? '')));
              $bioData2 = [
                  'name'  => $indira['name'],
                  'about' => $indira['about'],
                  'skills'=> $skillsArr2
              ];
              ?>
              <button class="btn" data-bio='<?php echo h(json_encode($bioData2, JSON_UNESCAPED_UNICODE)); ?>'>BIO</button>
            </div>
          </div>
        </article>
      </div>

      <!-- Карусель друзей -->
      <section aria-label="Карусель друзей">
        <h3 class="title" style="font-size:20px;margin:12px 0 4px">Карусель друзей</h3>
        <div class="carousel" id="friendsCarousel"><div class="track" id="carTrack"></div></div>

        <!-- Форма добавления (с пресетами) -->
        <div class="controls" style="flex-wrap:wrap">
          <input class="btn" id="carName" placeholder="Имя" style="min-width:140px"/>

          <select class="btn" id="carRoleSel" title="Роль">
            <option value="Специалист по мемам" selected>Специалист по мемам</option>
            <option value="Гуру сарказма">Гуру сарказма</option>
            <option value="Инфлюенсер улыбок">Инфлюенсер улыбок</option>
            <option value="Фея шуток">Фея шуток</option>
            <option value="Мем-архитектор">Мем-архитектор</option>
            <option value="__custom__">Другое…</option>
          </select>
          <input class="btn" id="carRoleCustom" placeholder="Своя роль" style="display:none;min-width:180px"/>

          <select class="btn" id="carMedalSel" title="Медаль">
            <option value="⭐ Meme Star" selected>⭐ Meme Star</option>
            <option value="💎 Crypto Royal">💎 Crypto Royal</option>
            <option value="👑 CEO of LOL">👑 CEO of LOL</option>
            <option value="🔥 Roast Master">🔥 Roast Master</option>
            <option value="😎 Vibe Dealer">😎 Vibe Dealer</option>
            <option value="__custom__">Другое…</option>
          </select>
          <input class="btn" id="carMedalCustom" placeholder="Своя медаль" style="display:none;min-width:180px"/>

          <span style="position:relative;display:inline-block">
            <span class="btn">Выберите фото</span>
            <input type="file" id="carPhoto" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer"/>
          </span>
          <button class="btn primary" id="carAddBtn">Добавить в карусель</button>
        </div>
      </section>

      <!-- Совместное фото -->
      <div class="joint" aria-label="Совместное фото и подпись">
        <div class="meme-stage" id="memeStage">
          <img id="img-joint" src="images/3.png" alt="Катя и Индира вместе"/>
          <div class="meme-text" id="memeText">«Лежим в куче мемов — работаем на репутацию»</div>
          <input type="file" id="file-joint" accept="image/*"/>
        </div>
        <div>
          <div class="caption">Совместное предприятие «Катиндирбанк». Кликни по фото, чтобы заменить.</div>
          <div class="controls" style="margin-top:8px;flex-wrap:wrap">
            <input class="btn" id="memeInput" value="Долистались до капитала смеха" style="min-width:220px;flex:1"/>
            <button class="btn" id="randCaption2">🎲</button>
            <button class="btn primary" id="applyCaption">Обновить подпись</button>
          </div>
        </div>
      </div>

      <!-- Цитаты -->
      <div class="quotes">
        <div class="quote">📈 KatyaCoin ↑ +200% — держим до следующего смешка.</div>
        <div class="quote">🧾 Проспект смеха: «Максимальный риск — прихватит живот».</div>
        <div class="quote">💼 Лицензия на мемы: бессрочно, продление лайками.</div>
      </div>

      <!-- Доп. фото -->
      <div class="gallery">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:8px">
          <div class="subtitle">Доп. фото</div>
          <div class="controls">
            <button class="btn" id="addThumbBtn">Добавить фото</button>
            <button class="btn" id="resetThumbs">Сбросить фото</button>
            <input type="file" id="addThumbInput" accept="image/*" multiple style="display:none"/>
          </div>
        </div>
        <div class="thumbs" id="thumbs"></div>
      </div>
    </section>
  </div>

  <footer>© Цой Артём TSOY.IN Project 2025 y.</footer>

  <!-- Contacts modal -->
  <div class="modal" id="contactsModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="contactsTitle">
      <h3 id="contactsTitle">Наши контакты</h3>
      <div class="modal-row"><label for="cEmail">Почта</label><input class="btn" id="cEmail" type="text" value="hello@katindir.agency" readonly/></div>
      <div class="modal-row"><label for="cPhone">Телефон</label><input class="btn" id="cPhone" type="text" value="+7 999 000-00-00" readonly/></div>
      <div class="modal-row"><label for="cAddr">Адрес</label><input class="btn" id="cAddr" type="text" value="Тюмень, ул. Креативная, 1" readonly/></div>
      <div class="modal-row"><label for="cTg">Telegram</label><input class="btn" id="cTg" type="text" value="@katindirnet" readonly/></div>
      <div class="modal-actions"><button class="btn" id="contactsClose">Закрыть</button></div>
    </div>
  </div>

  <!-- Tools modal -->
  <div class="modal" id="toolsModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="toolsTitle">
      <h3 id="toolsTitle">Инструменты</h3>
      <div class="tools-list">
        <!-- CV builder tool -->
        <a href="cv.php" class="tool-link">
          <span class="tool-logo">CV</span>
          <span class="sep" aria-hidden="true"></span>
          <span class="tool-desc">CV</span>
        </a>
        <!-- Shorty link shortener tool (integrated from the Shorty project).  This link
             opens the URL shortener in a new tab.  The shortener is considered
             part of the overall toolkit. -->
        <a href="https://l.tsoy.in" class="tool-link" target="_blank" rel="noopener">
          <span class="tool-logo">🔗</span>
          <span class="sep" aria-hidden="true"></span>
          <span class="tool-desc">Shorty</span>
        </a>
        <!-- Personal account / login.  Display a login link for guests and a
             personal account link for authenticated users.  This uses PHP
             embedded in HTML to determine which link to show. -->
        <?php if ($u): ?>
        <a href="/id.php" class="tool-link">
          <span class="tool-logo">👤</span>
          <span class="sep" aria-hidden="true"></span>
          <span class="tool-desc">Личный кабинет</span>
        </a>
        <?php else: ?>
        <a href="/id.php" class="tool-link">
          <span class="tool-logo">🔐</span>
          <span class="sep" aria-hidden="true"></span>
          <span class="tool-desc">Войти</span>
        </a>
        <?php endif; ?>
      </div>
      <div class="modal-actions"><button class="btn" id="toolsClose">Закрыть</button></div>
    </div>
  </div>

  <!-- BIO modal -->
  <div class="modal" id="bioModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="bioTitle">
      <h3 id="bioTitle">BIO</h3>
      <div style="display:flex; gap:12px; align-items:flex-start">
        <img id="bioPhoto" alt="" style="width:84px;height:84px;border-radius:12px;object-fit:cover;display:none;border:1px solid rgba(255,255,255,.1)"/>
        <div style="flex:1">
          <p id="bioAbout" style="margin:6px 0 8px;opacity:.95"></p>
          <div id="bioTags" class="tagbar"></div>
        </div>
      </div>
      <div class="modal-actions"><button class="btn" id="bioClose">Закрыть</button></div>
    </div>
  </div>

  <!-- FAB + Settings panel -->
  <button class="fab" id="fab" title="Настройки">🐱</button>
  <div class="panel" id="panel">
    <h3>Настройки</h3>

    <!-- Тема -->
    <div class="row"><span>Тема</span><div>
          <button class="pill" data-theme="neon">Неон</button>
          <button class="pill" data-theme="classic">Классика</button>
          <button class="pill" data-theme="classic-plus">Classic+</button>
          <button class="pill" data-theme="neon-plus">Neon+</button>
          <button class="pill" data-theme="light">Light</button>
    </div></div>

    <!-- Акцент -->
    <div class="row"><span>Акцент</span><div>
      <button class="pill" data-accent="aqua">Aqua</button>
      <button class="pill" data-accent="pink">Pink</button>
      <button class="pill" data-accent="violet">Violet</button>
      <button class="pill" data-accent="lime">Lime</button>
      <button class="pill" data-accent="vanilla">Vanilla 🍦</button>
    </div></div>

    <!-- Кошачий дзен -->
    <div class="row"><span>Кошачий дзен</span><div>
      <button class="pill" id="zenOn">Вкл</button>
      <button class="pill" id="zenOff">Выкл</button>
    </div></div>

    <!-- Котики + плотность -->
    <div class="row"><span>Котики с неба</span><div>
      <button class="pill" id="catsOn">Вкл</button>
      <button class="pill" id="catsOff">Выкл</button>
    </div></div>
    <div class="row"><span>Плотность котиков</span>
      <input class="range" id="catsDensity" type="range" min="60" max="800" step="20" value="260"/>
    </div>

    <!-- Музыка -->
    <div class="row"><span>Музыка</span><div>
      <button class="pill" id="soundOn">Вкл</button>
      <button class="pill" id="soundOff">Выкл</button>
    </div></div>
    <div class="row"><span>Громкость</span>
      <input class="range" id="vol" type="range" min="0" max="1" step="0.01" value="0.6"/>
    </div>

    <!-- Тикер -->
    <div class="row"><span>Скорость тикера</span>
      <input class="range" id="tickerSpeed" type="range" min="8" max="40" step="1" value="24"/>
    </div>

    <div class="row" style="justify-content:flex-end">
      <button class="btn" id="resetSettings">Сбросить</button>
      <button class="btn primary" id="closePanel">Закрыть</button>
    </div>
  </div>

<script src="js/index.js"></script>
</body>
</html>