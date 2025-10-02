<?php
// Include authentication helpers to determine if the user is logged in.  This
// allows the CV constructor page to display a link to the personal account
// or login page within the header controls.
require_once __DIR__ . '/helpers.php';
$u = user();
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Катиндирнет — CV Конструктор</title>

  <!-- Цвет панелей браузера (совпадает с главным сайтом) -->
  <meta name="theme-color" content="#0a0e14">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- (если уже делаешь PWA) -->
  <link rel="manifest" href="/manifest.webmanifest">
  <link rel="icon" href="/icons/icon-192.png" sizes="192x192">
  <link rel="apple-touch-icon" href="/icons/icon-192.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/cv.css">
  <link rel="stylesheet" href="css/tokens.css">
  <link rel="stylesheet" href="css/theme-classic-plus.css">
  <link rel="stylesheet" href="css/theme-neon-plus.css">
  <link rel="stylesheet" href="css/theme-light.css">
  <link rel="stylesheet" href="css/print.css" media="print">
  <script src="js/theme.js"></script>
</head>
<body>
  <div class="wrap">
    <header>
      <div class="brand">
        <div class="logo">КА</div>
        <div>
          <h1>Катиндирнет</h1>
          <div class="subtitle">CV Конструктор</div>
        </div>
      </div>
      <div class="controls">
        <a class="btn" href="/">На главную</a>
        <button class="btn ghost" id="loadCv">Загрузить CV</button>
        <button class="btn" id="clearDraft">Сброс</button>
        <button class="btn ghost" id="settingsBtn" title="Настройки">⚙️</button>
        <button class="btn" id="searchCv">🔍 Поиск</button>
        <input type="file" id="loadFile" accept=".html,text/html" style="display:none">
        <!-- Personal account or login link.  When a user is logged in, show
             "ЛК" (личный кабинет).  Otherwise show "Войти". -->
        <?php if ($u): ?>
          <a class="btn ghost" href="/id.php" title="Личный кабинет">👤</a>
        <?php else: ?>
          <a class="btn ghost" href="/id.php" title="Войти">🔐</a>
        <?php endif; ?>
      </div>
    </header>

    <div class="settings">
      <div class="setrow"><span>Тема</span>
        <div class="pillbar">
          <button class="pill" data-theme="neon">Неон</button>
          <button class="pill" data-theme="classic">Классика</button>
          <button class="pill" data-theme="classic-plus">Classic+</button>
          <button class="pill" data-theme="neon-plus">Neon+</button>
          <button class="pill" data-theme="light">Light</button>
        </div>
      </div>
      <div class="setrow"><span>Акцент</span>
        <div class="pillbar">
          <button class="pill" data-accent="aqua">Aqua</button>
          <button class="pill" data-accent="pink">Pink</button>
          <button class="pill" data-accent="violet">Violet</button>
          <button class="pill" data-accent="lime">Lime</button>
          <button class="pill" data-accent="vanilla">Vanilla 🍦</button>
        </div>
      </div>
    </div>

    <!-- Блок 1. Профиль -->
    <section class="section">
      <h2>Профиль</h2>
      <div class="progress"><div id="profileProgress"></div></div>
      <div class="card">
        <label class="avatar" title="Нажми, чтобы выбрать фото">
          <img id="photoPreview" src="" alt="Фото" style="display:none">
          <input type="file" id="photoInput" accept="image/*">
        </label>
        <div style="flex:1">
          <div class="form-grid">
            <div>
              <label>ФИО</label>
              <input id="fio" type="text" placeholder="Фамилия Имя Отчество">
            </div>
            <div class="two">
              <div>
                <label>Дата рождения</label>
                <input id="birth" type="date">
              </div>
              <div>
                <label>Пол</label>
                <select id="gender">
                  <option value="">—</option>
                  <option>Мужской</option>
                  <option>Женский</option>
                  <option>Другое</option>
                </select>
              </div>
            </div>
            <div>
              <label>Гражданство</label>
              <input id="citizenship" type="text" placeholder="напр. Казахстан">
            </div>
            <div>
              <label>Местоположение</label>
              <input id="location" type="text" placeholder="Город, страна">
            </div>
            <div>
              <label>Контакты: Email</label>
              <input id="email" type="email" placeholder="you@example.com">
            </div>
            <div class="two">
              <div>
                <label>Телефон</label>
                <input id="phone" type="tel" placeholder="+7 999 ...">
              </div>
              <div>
                <label>Telegram</label>
                <input id="telegram" type="text" placeholder="@nickname">
              </div>
            </div>
            <div>
              <label>Сайт/Портфолио</label>
              <input id="site" type="url" placeholder="https://...">
            </div>
            <div>
              <label>Скиллы</label>
              <div class="row">
                <input id="skillInput" type="text" placeholder="нажми Enter, чтобы добавить">
                <button class="btn" id="addSkill">Добавить</button>
              </div>
              <div class="tagbar" id="skills"></div>
            </div>
            <div>
              <label>Языки</label>
              <div class="row">
                <input id="langInput" type="text" placeholder="язык">
                <select id="langLevel">
                  <option value="A1">A1</option>
                  <option value="A2">A2</option>
                  <option value="B1">B1</option>
                  <option value="B2">B2</option>
                  <option value="C1">C1</option>
                  <option value="C2">C2</option>
                  <option value="Native">Native</option>
                </select>
                <button class="btn" id="addLang">Добавить</button>
              </div>
              <div class="tagbar" id="languages"></div>
            </div>
            <div style="grid-column:1/-1">
              <label>О себе (как в BIO)</label>
              <textarea id="about" placeholder="Пара фраз про тебя, ценности, стиль работы, хайлайты."></textarea>
            </div>
            <div id="pinField" style="display:none">
              <label>PIN для редактирования (4 цифры)</label>
              <div id="pinBox" class="pin-inputs" style="justify-content:flex-start">
                <input type="password" maxlength="1" inputmode="numeric"/>
                <input type="password" maxlength="1" inputmode="numeric"/>
                <input type="password" maxlength="1" inputmode="numeric"/>
                <input type="password" maxlength="1" inputmode="numeric"/>
                <button class="btn ghost" id="resetPin" type="button" style="margin-left:10px">Сброс</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="hint">Фото встроится прямо в HTML (base64), чтобы страница CV была автономной.</div>
    </section>

    <!-- Блок 2. Карьера -->
    <section class="section">
      <h2>Карьера</h2>
      <div id="careerList" class="list"></div>
      <div class="actions">
        <button class="btn" id="addCareer">Добавить место работы</button>
      </div>
    </section>

    <!-- Блок 3. Образование / Курсы -->
    <section class="section">
      <h2>Образование и курсы</h2>
      <div id="eduList" class="list"></div>
      <div class="actions">
        <button class="btn" id="addEdu">Добавить образование/курс</button>
      </div>
    </section>

    <!-- Блок 4. Интересы -->
    <section class="section">
      <h2>Интересы</h2>
      <textarea id="interests" placeholder="Хобби, дополнительная информация"></textarea>
    </section>

    <!-- Кнопки генерации -->
    <section class="section" style="display:flex;justify-content:space-between;align-items:center;gap:10px">
      <h2 class="title">Готово? Сгенерируй CV</h2>
      <div class="controls">
        <button class="btn" id="previewBtn">Предпросмотр</button>
        <button class="btn" id="saveBtn">Выгрузить в HTML</button>
        <button class="btn primary" id="saveServerBtn">Сохранить</button>
        <button class="btn danger" id="deleteServerBtn" style="display:none">Удалить CV</button>
      </div>
    </section>

  </div>

  <footer>© Цой Артём TSOY.IN Project 2025 y.</footer>

  <!-- Модал код/превью -->
  <div class="modal" id="codeModal" aria-hidden="true">
    <div class="modal-card">
      <h3>Сгенерированный HTML</h3>
      <textarea id="codeBox" class="code" readonly></textarea>
      <div class="actions">
        <button class="btn" id="copyCode">Копировать</button>
        <button class="btn primary" id="openPreview">Открыть предпросмотр</button>
        <button class="btn" id="closeModal">Закрыть</button>
      </div>
    </div>
  </div>

  <div class="modal" id="searchModal" aria-hidden="true">
    <div class="modal-card">
      <h3>Поиск CV</h3>
      <input id="searchInput" class="btn" placeholder="введите ваше имя" style="width:100%;margin-bottom:10px">
      <div id="searchResults" class="search-grid"></div>
      <div class="actions" style="justify-content:flex-end">
        <button class="btn" id="closeSearch">Закрыть</button>
      </div>
    </div>
  </div>

  <div class="modal" id="pinModal" aria-hidden="true">
    <div class="modal-card">
      <h3 id="pinTitle">PIN</h3>
      <div class="pin-inputs">
        <input type="password" maxlength="1" inputmode="numeric"/>
        <input type="password" maxlength="1" inputmode="numeric"/>
        <input type="password" maxlength="1" inputmode="numeric"/>
        <input type="password" maxlength="1" inputmode="numeric"/>
      </div>
      <div class="actions" style="justify-content:flex-end">
        <button class="btn" id="pinCancel">Отмена</button>
        <button class="btn primary" id="pinOk">OK</button>
      </div>
    </div>
  </div>

<script src="js/cv.js"></script>
</body>
</html>
