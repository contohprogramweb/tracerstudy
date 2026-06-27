<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4A90D9">
    <title><?= htmlspecialchars($survey->title) ?> - Survey Tracer Study</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= base_url('manifest.json') ?>">
    
    <!-- Bootstrap 5 Mobile First -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4A90D9;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-bottom: 80px;
        }
        
        .survey-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 15px;
        }
        
        .card-survey {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .survey-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .survey-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        /* Progress Bar */
        .progress-container {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
            background: #e9ecef;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--primary-color), #5a9fd9);
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
            display: flex;
            justify-content: space-between;
        }
        
        /* Question Card */
        .question-card {
            padding: 25px 20px;
            display: none;
        }
        
        .question-card.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .question-number {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .question-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }
        
        .required-mark {
            color: var(--danger-color);
        }
        
        /* Form Controls */
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 217, 0.25);
        }
        
        .option-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .option-card:hover {
            border-color: var(--primary-color);
            background: #f8f9fa;
        }
        
        .option-card.selected {
            border-color: var(--primary-color);
            background: rgba(74, 144, 217, 0.1);
        }
        
        .option-card input[type="radio"],
        .option-card input[type="checkbox"] {
            margin-right: 10px;
        }
        
        /* Rating */
        .rating-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .rating-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #e9ecef;
            background: white;
            font-size: 1.2rem;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        
        .rating-btn:hover,
        .rating-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Navigation Buttons */
        .nav-buttons {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 15px;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            gap: 10px;
            z-index: 1000;
        }
        
        .btn-nav {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            flex: 1;
            max-width: 150px;
        }
        
        .btn-prev {
            background: #6c757d;
            color: white;
            border: none;
        }
        
        .btn-next {
            background: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-submit {
            background: var(--success-color);
            color: white;
            border: none;
        }
        
        /* Status Indicators */
        .status-indicator {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            z-index: 1001;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .status-online {
            background: #d4edda;
            color: #155724;
        }
        
        .status-offline {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-saving {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-saved {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .blink {
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Question Navigator */
        .question-navigator {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            padding: 10px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .nav-dot {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .nav-dot.completed {
            background: var(--success-color);
            color: white;
        }
        
        .nav-dot.current {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }
        
        .nav-dot:hover {
            transform: scale(1.1);
        }
        
        /* Modal */
        .modal-header {
            background: var(--primary-color);
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .survey-header h2 {
                font-size: 1.2rem;
            }
            
            .question-text {
                font-size: 1rem;
            }
            
            .nav-buttons {
                flex-direction: column;
            }
            
            .btn-nav {
                max-width: 100%;
            }
            
            .rating-btn {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Online/Offline Status -->
    <div id="connectionStatus" class="status-indicator status-online">
        <i class="fas fa-wifi"></i>
        <span>Online</span>
    </div>
    
    <!-- Auto-save Status -->
    <div id="saveStatus" class="status-indicator status-saved" style="top: 60px;">
        <i class="fas fa-check-circle"></i>
        <span>Data tersimpan</span>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>
    
    <div class="survey-container">
        <div class="card-survey">
            <!-- Header -->
            <div class="survey-header">
                <h2><i class="fas fa-clipboard-list"></i> <?= htmlspecialchars($survey->title) ?></h2>
                <p class="mb-0"><?= htmlspecialchars($survey->description ?? '') ?></p>
            </div>
            
            <!-- Progress Bar -->
            <div class="progress-container">
                <div class="progress">
                    <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="progress-text">
                    <span id="progressPercent">0%</span>
                    <span>Pertanyaan <span id="currentQuestionNum">0</span> dari <?= $total_questions ?></span>
                </div>
            </div>
            
            <!-- Questions -->
            <form id="surveyForm" method="POST">
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $csrf_token ?>">
                
                <?php foreach ($questions as $index => $question): ?>
                <div class="question-card" data-question-id="<?= $question['id'] ?>" data-index="<?= $index ?>">
                    <div class="mb-4">
                        <span class="question-number"><?= $index + 1 ?></span>
                        <span class="question-text">
                            <?= htmlspecialchars($question['question_text']) ?>
                            <?php if ($question['is_required']): ?>
                                <span class="required-mark">*</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="answer-container">
                        <?php
                        // Render based on question type
                        switch ($question['type']) {
                            case 'short_answer':
                                echo '<input type="text" class="form-control" name="answers[' . $question['id'] . ']" 
                                      data-question-id="' . $question['id'] . '" 
                                      placeholder="Jawaban singkat..." ' . ($question['is_required'] ? 'required' : '') . '>';
                                break;
                                
                            case 'long_answer':
                                echo '<textarea class="form-control" name="answers[' . $question['id'] . ']" 
                                      data-question-id="' . $question['id'] . '" rows="4" 
                                      placeholder="Tulis jawaban Anda..." ' . ($question['is_required'] ? 'required' : '') . '></textarea>';
                                break;
                                
                            case 'multiple_choice':
                            case 'dropdown':
                                $options = $question['parsed_options'];
                                if ($question['type'] === 'dropdown') {
                                    echo '<select class="form-select" name="answers[' . $question['id'] . ']" 
                                          data-question-id="' . $question['id'] . '" ' . ($question['is_required'] ? 'required' : '') . '>';
                                    echo '<option value="">-- Pilih jawaban --</option>';
                                    foreach ($options as $opt) {
                                        echo '<option value="' . htmlspecialchars($opt) . '">' . htmlspecialchars($opt) . '</option>';
                                    }
                                    echo '</select>';
                                } else {
                                    foreach ($options as $opt) {
                                        echo '<label class="option-card d-block">';
                                        echo '<input type="radio" name="answers[' . $question['id'] . ']" value="' . htmlspecialchars($opt) . '" 
                                              data-question-id="' . $question['id'] . '" ' . ($question['is_required'] ? 'required' : '') . '>';
                                        echo htmlspecialchars($opt);
                                        echo '</label>';
                                    }
                                }
                                break;
                                
                            case 'checkbox':
                                $options = $question['parsed_options'];
                                foreach ($options as $opt) {
                                    echo '<label class="option-card d-block">';
                                    echo '<input type="checkbox" name="answers[' . $question['id'] . '][]" value="' . htmlspecialchars($opt) . '" 
                                          data-question-id="' . $question['id'] . '">';
                                    echo htmlspecialchars($opt);
                                    echo '</label>';
                                }
                                break;
                                
                            case 'rating':
                                echo '<div class="rating-group">';
                                for ($i = 1; $i <= 5; $i++) {
                                    echo '<button type="button" class="rating-btn" data-value="' . $i . '" 
                                          data-question-id="' . $question['id'] . '">' . $i . '</button>';
                                }
                                echo '</div>';
                                echo '<input type="hidden" name="answers[' . $question['id'] . ']" id="rating_' . $question['id'] . '" 
                                      ' . ($question['is_required'] ? 'required' : '') . '>';
                                break;
                                
                            case 'date':
                                echo '<input type="date" class="form-control" name="answers[' . $question['id'] . ']" 
                                      data-question-id="' . $question['id'] . '" ' . ($question['is_required'] ? 'required' : '') . '>';
                                break;
                                
                            case 'number':
                                echo '<input type="number" class="form-control" name="answers[' . $question['id'] . ']" 
                                      data-question-id="' . $question['id'] . '" 
                                      placeholder="Angka..." ' . ($question['is_required'] ? 'required' : '') . '>';
                                break;
                                
                            default:
                                echo '<input type="text" class="form-control" name="answers[' . $question['id'] . ']" 
                                      data-question-id="' . $question['id'] . '" ' . ($question['is_required'] ? 'required' : '') . '>';
                        }
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </form>
            
            <!-- Question Navigator -->
            <div class="question-navigator" id="questionNavigator">
                <?php foreach ($questions as $index => $question): ?>
                <div class="nav-dot" data-index="<?= $index ?>" title="Pertanyaan <?= $index + 1 ?>">
                    <?= $index + 1 ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <button type="button" id="btnPrev" class="btn btn-nav btn-prev" disabled>
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
        <button type="button" id="btnNext" class="btn btn-nav btn-next">
            Lanjut <i class="fas fa-arrow-right"></i>
        </button>
        <button type="button" id="btnSubmit" class="btn btn-nav btn-submit" style="display: none;">
            <i class="fas fa-paper-plane"></i> Kirim Survey
        </button>
    </div>
    
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-check-circle"></i> Berhasil!</h5>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Survey Berhasil Dikirim!</h4>
                    <p>Terima kasih telah mengisi survey tracer study.</p>
                    <div id="certificateSection" style="display: none;" class="mt-3">
                        <a id="certificateLink" href="#" class="btn btn-primary" target="_blank">
                            <i class="fas fa-certificate"></i> Download Sertifikat
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="window.location.href='<?= base_url('alumni/dashboard') ?>'">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Error</h5>
                </div>
                <div class="modal-body" id="errorModalBody">
                    <!-- Error message will be inserted here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Workbox for PWA -->
    <script src="https://storage.googleapis.com/workbox-cdn/releases/6.5.4/workbox-sw.js"></script>
    
    <script>
    $(document).ready(function() {
        // ==================== CONFIGURATION ====================
        const SURVEY_ID = <?= $survey->id ?>;
        const ALUMNI_ID = <?= $alumni->id ?>;
        const TOTAL_QUESTIONS = <?= $total_questions ?>;
        const AUTOSAVE_INTERVAL = 30000; // 30 seconds
        const BASE_URL = '<?= base_url() ?>';
        
        // ==================== STATE ====================
        let currentQuestionIndex = 0;
        let answers = {};
        let isOnline = navigator.onLine;
        let autoSaveTimer = null;
        let hasLocalData = false;
        
        // ==================== INITIALIZATION ====================
        initSurvey();
        
        function initSurvey() {
            updateConnectionStatus();
            showQuestion(currentQuestionIndex);
            updateProgress();
            updateNavButtons();
            checkForSavedProgress();
            startAutoSave();
            setupEventListeners();
            registerServiceWorker();
        }
        
        // ==================== SERVICE WORKER (PWA) ====================
        function registerServiceWorker() {
            if ('serviceWorker' in navigator && window.Workbox) {
                workbox.setConfig({ debug: false });
                
                workbox.precaching.precacheAndRoute([]);
                
                workbox.routing.registerRoute(
                    ({request}) => request.destination === 'script',
                    new workbox.strategies.StaleWhileRevalidate()
                );
                
                workbox.routing.registerRoute(
                    ({request}) => request.destination === 'style',
                    new workbox.strategies.StaleWhileRevalidate()
                );
                
                workbox.routing.registerRoute(
                    /\.(?:png|jpg|jpeg|svg|gif)$/,
                    new workbox.strategies.CacheFirst({
                        cacheName: 'images-cache'
                    })
                );
                
                console.log('Service Worker registered');
            }
        }
        
        // ==================== CONNECTION STATUS ====================
        function updateConnectionStatus() {
            isOnline = navigator.onLine;
            const $status = $('#connectionStatus');
            
            if (isOnline) {
                $status.removeClass('status-offline').addClass('status-online');
                $status.html('<i class="fas fa-wifi"></i> <span>Online</span>');
                
                // Sync local data if available
                if (hasLocalData) {
                    syncLocalData();
                }
            } else {
                $status.removeClass('status-online').addClass('status-offline');
                $status.html('<i class="fas fa-wifi-slash"></i> <span>Offline</span>');
                showSaveStatus('offline', 'Data tersimpan lokal');
            }
        }
        
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        
        // ==================== NAVIGATION ====================
        function showQuestion(index) {
            $('.question-card').removeClass('active');
            $(`.question-card[data-index="${index}"]`).addClass('active');
            
            // Update navigator dots
            $('.nav-dot').removeClass('current');
            $(`.nav-dot[data-index="${index}"]`).addClass('current');
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            currentQuestionIndex = index;
            updateProgress();
            updateNavButtons();
        }
        
        function updateNavButtons() {
            const $btnPrev = $('#btnPrev');
            const $btnNext = $('#btnNext');
            const $btnSubmit = $('#btnSubmit');
            
            $btnPrev.prop('disabled', currentQuestionIndex === 0);
            
            if (currentQuestionIndex === TOTAL_QUESTIONS - 1) {
                $btnNext.hide();
                $btnSubmit.show();
            } else {
                $btnNext.show();
                $btnSubmit.hide();
            }
        }
        
        // ==================== PROGRESS ====================
        function updateProgress() {
            const answeredCount = Object.keys(answers).filter(k => answers[k] !== '' && answers[k] !== null).length;
            const percent = Math.round((answeredCount / TOTAL_QUESTIONS) * 100);
            
            $('#progressBar').css('width', percent + '%');
            $('#progressPercent').text(percent + '%');
            $('#currentQuestionNum').text(currentQuestionIndex + 1);
            
            // Update navigator dots
            $('.nav-dot').each(function() {
                const idx = parseInt($(this).data('index'));
                const qId = $(`.question-card[data-index="${idx}"]`).data('question-id');
                
                if (answers[qId] && answers[qId] !== '') {
                    $(this).addClass('completed');
                } else {
                    $(this).removeClass('completed');
                }
            });
        }
        
        // ==================== ANSWER HANDLING ====================
        function setupEventListeners() {
            // Text inputs
            $(document).on('input', 'input[type="text"], input[type="number"], input[type="email"], textarea, input[type="date"]', function() {
                const qId = $(this).data('question-id');
                answers[qId] = $(this).val();
                updateProgress();
                triggerAutoSave();
            });
            
            // Radio buttons
            $(document).on('change', 'input[type="radio"]', function() {
                const qId = $(this).data('question-id');
                answers[qId] = $(this).val();
                
                // Remove selected class from siblings
                $(`input[name="answers[${qId}]"]`).closest('.option-card').removeClass('selected');
                $(this).closest('.option-card').addClass('selected');
                
                updateProgress();
                triggerAutoSave();
                handleLogicJump(qId, $(this).val());
            });
            
            // Checkboxes
            $(document).on('change', 'input[type="checkbox"]', function() {
                const qId = $(this).data('question-id');
                const checked = [];
                
                $(`input[name="answers[${qId}][]"]:checked`).each(function() {
                    checked.push($(this).val());
                });
                
                answers[qId] = checked;
                updateProgress();
                triggerAutoSave();
            });
            
            // Rating buttons
            $(document).on('click', '.rating-btn', function() {
                const qId = $(this).data('question-id');
                const value = $(this).data('value');
                
                $(`.rating-btn[data-question-id="${qId}"]`).removeClass('active');
                $(this).addClass('active');
                
                $(`#rating_${qId}`).val(value);
                answers[qId] = value;
                
                updateProgress();
                triggerAutoSave();
            });
            
            // Select dropdown
            $(document).on('change', 'select', function() {
                const qId = $(this).data('question-id');
                answers[qId] = $(this).val();
                updateProgress();
                triggerAutoSave();
                handleLogicJump(qId, $(this).val());
            });
            
            // Navigation buttons
            $('#btnNext').click(function() {
                if (validateCurrentQuestion()) {
                    showQuestion(currentQuestionIndex + 1);
                }
            });
            
            $('#btnPrev').click(function() {
                showQuestion(currentQuestionIndex - 1);
            });
            
            $('#btnSubmit').click(function() {
                submitSurvey();
            });
            
            // Navigator dots
            $('.nav-dot').click(function() {
                const idx = parseInt($(this).data('index'));
                showQuestion(idx);
            });
        }
        
        function validateCurrentQuestion() {
            const currentCard = $(`.question-card[data-index="${currentQuestionIndex}"]`);
            const requiredInputs = currentCard.find('[required]');
            let isValid = true;
            
            requiredInputs.each(function() {
                const $input = $(this);
                let value;
                
                if (this.type === 'checkbox') {
                    value = $(`input[name="${$input.attr('name')}"]:checked`).length;
                } else {
                    value = $input.val();
                }
                
                if (!value || value === '') {
                    isValid = false;
                    $input.addClass('is-invalid');
                } else {
                    $input.removeClass('is-invalid');
                }
            });
            
            if (!isValid) {
                showError('Mohon lengkapi jawaban yang wajib diisi');
            }
            
            return isValid;
        }
        
        // ==================== LOGIC JUMP ====================
        function handleLogicJump(questionId, answer) {
            // Client-side logic jump (for better UX)
            // Server will validate on submission
            const currentCard = $(`.question-card[data-question-id="${questionId}"]`);
            const questionData = <?= json_encode($questions) ?>;
            const currentQuestion = questionData.find(q => q.id == questionId);
            
            if (currentQuestion && currentQuestion.has_logic) {
                currentQuestion.logics.forEach(logic => {
                    if (logic.condition_value == answer) {
                        // Jump to target question
                        const targetIndex = $(`.question-card[data-question-id="${logic.target_question_id}"]`).data('index');
                        if (targetIndex !== undefined) {
                            setTimeout(() => {
                                showQuestion(targetIndex);
                            }, 500);
                        }
                    }
                });
            }
        }
        
        // ==================== AUTO-SAVE ====================
        function startAutoSave() {
            autoSaveTimer = setInterval(triggerAutoSave, AUTOSAVE_INTERVAL);
        }
        
        function triggerAutoSave() {
            if (Object.keys(answers).length === 0) return;
            
            showSaveStatus('saving', 'Menyimpan...');
            
            const data = {
                answers: answers,
                current_question: currentQuestionIndex,
                progress_percent: Math.round((Object.keys(answers).length / TOTAL_QUESTIONS) * 100),
                '<?= $this->security->get_csrf_token_name() ?>': '<?= $csrf_token ?>'
            };
            
            if (isOnline) {
                // Save to server
                $.ajax({
                    url: BASE_URL + 'survey_builder/survey/autosave/' + SURVEY_ID,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        showSaveStatus('saved', 'Data tersimpan');
                        // Also save to localStorage as backup
                        saveToLocalStorage();
                    },
                    error: function() {
                        // Fallback to localStorage
                        saveToLocalStorage();
                        showSaveStatus('offline', 'Data tersimpan lokal');
                    }
                });
            } else {
                // Offline - save to localStorage only
                saveToLocalStorage();
                showSaveStatus('offline', 'Data tersimpan lokal');
            }
        }
        
        function saveToLocalStorage() {
            const saveData = {
                survey_id: SURVEY_ID,
                alumni_id: ALUMNI_ID,
                answers: answers,
                current_question: currentQuestionIndex,
                timestamp: Date.now()
            };
            
            localStorage.setItem('survey_progress_' + SURVEY_ID, JSON.stringify(saveData));
            hasLocalData = true;
        }
        
        function loadFromLocalStorage() {
            const saved = localStorage.getItem('survey_progress_' + SURVEY_ID);
            
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    
                    // Check if still within 7 days (BR-SUR-005)
                    const sevenDays = 7 * 24 * 60 * 60 * 1000;
                    if (Date.now() - data.timestamp > sevenDays) {
                        localStorage.removeItem('survey_progress_' + SURVEY_ID);
                        return false;
                    }
                    
                    answers = data.answers || {};
                    currentQuestionIndex = data.current_question || 0;
                    
                    // Restore answers to form
                    restoreAnswers();
                    
                    hasLocalData = true;
                    return true;
                } catch (e) {
                    console.error('Error loading from localStorage:', e);
                    return false;
                }
            }
            
            return false;
        }
        
        function restoreAnswers() {
            // Restore text inputs
            Object.keys(answers).forEach(qId => {
                const value = answers[qId];
                const $input = $(`[data-question-id="${qId}"]`);
                
                if ($input.length) {
                    if ($input.is('input[type="radio"]')) {
                        $(`input[name="answers[${qId}]"][value="${value}"]`).prop('checked', true).trigger('change');
                    } else if ($input.is('input[type="checkbox"]')) {
                        if (Array.isArray(value)) {
                            value.forEach(v => {
                                $(`input[name="answers[${qId}][]"][value="${v}"]`).prop('checked', true);
                            });
                        }
                    } else if ($input.hasClass('rating-btn')) {
                        $(`.rating-btn[data-question-id="${qId}"][data-value="${value}"]`).addClass('active');
                        $(`#rating_${qId}`).val(value);
                    } else {
                        $input.val(value);
                    }
                }
            });
            
            updateProgress();
        }
        
        function checkForSavedProgress() {
            // Check database first
            $.ajax({
                url: BASE_URL + 'survey_builder/survey/resume/' + SURVEY_ID,
                type: 'GET',
                success: function(response) {
                    if (response.status === 'success' && response.progress) {
                        answers = response.progress.answers || {};
                        currentQuestionIndex = response.progress.current_question || 0;
                        restoreAnswers();
                        showSaveStatus('saved', 'Progress dilanjutkan');
                    } else if (response.status === 'not_found' || response.status === 'expired') {
                        // Try localStorage
                        if (loadFromLocalStorage()) {
                            showSaveStatus('saved', 'Data lokal ditemukan');
                        }
                    }
                },
                error: function() {
                    // Try localStorage as fallback
                    loadFromLocalStorage();
                }
            });
        }
        
        function syncLocalData() {
            const saved = localStorage.getItem('survey_progress_' + SURVEY_ID);
            
            if (saved && isOnline) {
                try {
                    const data = JSON.parse(saved);
                    
                    $.ajax({
                        url: BASE_URL + 'survey_builder/survey/autosave/' + SURVEY_ID,
                        type: 'POST',
                        data: {
                            answers: data.answers,
                            current_question: data.current_question,
                            progress_percent: Object.keys(data.answers).length / TOTAL_QUESTIONS * 100,
                            '<?= $this->security->get_csrf_token_name() ?>': '<?= $csrf_token ?>'
                        },
                        success: function() {
                            localStorage.removeItem('survey_progress_' + SURVEY_ID);
                            hasLocalData = false;
                            console.log('Local data synced successfully');
                        }
                    });
                } catch (e) {
                    console.error('Error syncing local data:', e);
                }
            }
        }
        
        function showSaveStatus(status, message) {
            const $status = $('#saveStatus');
            
            $status.removeClass('status-saving status-saved status-offline');
            
            switch (status) {
                case 'saving':
                    $status.addClass('status-saving blink');
                    break;
                case 'saved':
                    $status.addClass('status-saved').removeClass('blink');
                    break;
                case 'offline':
                    $status.addClass('status-offline').removeClass('blink');
                    break;
            }
            
            $status.html('<i class="fas fa-' + (status === 'saving' ? 'spinner fa-spin' : status === 'saved' ? 'check-circle' : 'wifi-slash') + '"></i> <span>' + message + '</span>');
            
            if (status === 'saved') {
                setTimeout(() => {
                    $status.removeClass('blink');
                }, 1000);
            }
        }
        
        // ==================== SUBMISSION ====================
        function submitSurvey() {
            // Validate all questions
            let isValid = true;
            const errors = [];
            
            $('.question-card').each(function() {
                const requiredInputs = $(this).find('[required]');
                
                requiredInputs.each(function() {
                    const $input = $(this);
                    const qId = $input.data('question-id');
                    let value;
                    
                    if (this.type === 'checkbox') {
                        value = $(`input[name="${$input.attr('name')}"]:checked`).length;
                    } else {
                        value = answers[qId];
                    }
                    
                    if (!value || (Array.isArray(value) && value.length === 0)) {
                        isValid = false;
                        errors.push(`Pertanyaan ${$(this).closest('.question-card').find('.question-number').text()} wajib diisi`);
                    }
                });
            });
            
            if (!isValid) {
                showError(errors.join('<br>'));
                return;
            }
            
            // Show loading
            $('#loadingOverlay').show();
            
            // Submit
            $.ajax({
                url: BASE_URL + 'survey_builder/survey/submit/' + SURVEY_ID,
                type: 'POST',
                data: {
                    answers: answers,
                    '<?= $this->security->get_csrf_token_name() ?>': '<?= $csrf_token ?>'
                },
                success: function(response) {
                    $('#loadingOverlay').hide();
                    
                    if (response.status === 'success') {
                        // Clear localStorage
                        localStorage.removeItem('survey_progress_' + SURVEY_ID);
                        
                        // Show success modal
                        if (response.certificate_url) {
                            $('#certificateLink').attr('href', response.certificate_url);
                            $('#certificateSection').show();
                        }
                        
                        $('#successModal').modal('show');
                    } else {
                        showError(response.message || 'Gagal mengirim survey');
                    }
                },
                error: function(xhr) {
                    $('#loadingOverlay').hide();
                    
                    let errorMsg = 'Gagal mengirim survey. Periksa koneksi internet Anda.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    
                    showError(errorMsg);
                }
            });
        }
        
        function showError(message) {
            $('#errorModalBody').html(message);
            $('#errorModal').modal('show');
        }
        
        // ==================== UTILITY ====================
        // Prevent accidental navigation
        window.onbeforeunload = function() {
            if (Object.keys(answers).length > 0) {
                return 'Anda memiliki progress yang belum disimpan. Yakin ingin keluar?';
            }
        };
    });
    </script>
</body>
</html>
