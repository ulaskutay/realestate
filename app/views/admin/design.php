<?php
/**
 * Tasarım Düzenleme - Tema Kod Editörü
 * Aktif temanın dosyalarını düzenleme sayfası
 */

// Aktif tema bilgisi
$activeTheme = $activeTheme ?? null;
$themeFiles = $themeFiles ?? [];
$selectedFile = $selectedFile ?? '';
$fileContent = $fileContent ?? '';
$customCss = $customCss ?? '';
$customJs = $customJs ?? '';
$customHead = $customHead ?? '';
$activeTab = $activeTab ?? 'files';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($title) ? esc_html($title) : 'Tasarım Düzenleme - CMS'; ?></title>
    
    <!-- Dark Mode - Sayfa yüklenmeden önce çalışmalı (FOUC önleme) -->
    <script>
        (function() {
            'use strict';
            const DARK_MODE_KEY = 'admin_dark_mode';
            const htmlElement = document.documentElement;
            let darkModePreference = null;
            try {
                const savedPreference = localStorage.getItem(DARK_MODE_KEY);
                if (savedPreference === 'dark' || savedPreference === 'light') {
                    darkModePreference = savedPreference === 'dark';
                }
            } catch (e) {}
            if (darkModePreference === null) {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    darkModePreference = true;
                } else {
                    darkModePreference = false;
                }
            }
            if (darkModePreference) {
                htmlElement.classList.add('dark');
            } else {
                htmlElement.classList.remove('dark');
            }
        })();
    </script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link href="<?php echo rtrim(site_url(), '/') . '/admin/css/admin-dashboard.css'; ?>" rel="stylesheet"/>
    
    <!-- Dark Mode Toggle Script -->
    <script src="<?php echo rtrim(site_url(), '/') . '/admin/js/dark-mode.js'; ?>"></script>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
    
    <!-- CodeMirror -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"],
                        "mono": ["JetBrains Mono", "Fira Code", "monospace"]
                    },
                },
            },
        }
    </script>
    
    <style>
        /* Prevent FOUC - Hide until Tailwind loads */
        .loading-hide { opacity: 0; }
        .loaded .loading-hide { opacity: 1; transition: opacity 0.2s ease; }
        
        /* Critical Layout Styles */
        body { margin: 0; background: #15202b; min-height: 100vh; }
        .font-display { font-family: 'Inter', sans-serif; }
        
        .CodeMirror {
            height: calc(100vh - 350px);
            min-height: 400px;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 14px;
            border-radius: 0 0 0.5rem 0.5rem;
        }
        .file-tree-item {
            cursor: pointer;
            transition: all 0.15s;
        }
        .file-tree-item:hover {
            background-color: rgba(19, 127, 236, 0.1);
        }
        .file-tree-item.active {
            background-color: rgba(19, 127, 236, 0.2);
            border-left: 3px solid #137fec;
        }
        .folder-toggle {
            transition: transform 0.2s;
        }
        .folder-toggle.open {
            transform: rotate(90deg);
        }
        .folder-content {
            display: none !important;
        }
        .folder-content.open {
            display: block !important;
        }
    </style>
    <script>
        // Mark body as loaded once Tailwind processes
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() { document.body.classList.add('loaded'); }, 50);
        });
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden loading-hide">
        <div class="flex min-h-screen">
            <?php 
            $currentPage = 'design';
            include __DIR__ . '/snippets/sidebar.php'; 
            ?>

            <main class="main-content-with-sidebar flex-1 p-6 lg:p-10 bg-gray-50 dark:bg-[#15202b]">
                <div class="layout-content-container flex flex-col w-full mx-auto max-w-7xl">
                    
                    <!-- Header -->
                    <header class="flex flex-col sm:flex-row flex-wrap justify-between items-start sm:items-center gap-4 mb-6">
                        <div class="flex flex-col gap-2">
                            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Tasarım Düzenleme</h1>
                            <p class="text-gray-500 dark:text-gray-400 text-base">
                                <?php if ($activeTheme): ?>
                                    <span class="inline-flex items-center gap-2">
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                        Aktif Tema: <strong class="text-gray-700 dark:text-gray-300"><?php echo esc_html($activeTheme['name']); ?></strong>
                                    </span>
                                <?php else: ?>
                                    <span class="text-yellow-600 dark:text-yellow-400">Aktif tema bulunamadı. Önce bir tema aktifleştirin.</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </header>

                    <!-- Message -->
                    <?php if (isset($message) && $message): ?>
                        <div class="mb-6 p-4 rounded-lg flex items-center gap-3 <?php echo $messageType === 'success' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'; ?>">
                            <span class="material-symbols-outlined"><?php echo $messageType === 'success' ? 'check_circle' : 'error'; ?></span>
                            <?php echo esc_html($message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($activeTheme): ?>
                    <!-- Tabs -->
                    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex space-x-8 overflow-x-auto" aria-label="Tabs">
                            <a href="<?php echo admin_url('design', ['tab' => 'files']); ?>" 
                               class="<?php echo $activeTab === 'files' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">folder_open</span>
                                Tema Dosyaları
                            </a>
                            <a href="<?php echo admin_url('design', ['tab' => 'css']); ?>" 
                               class="<?php echo $activeTab === 'css' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">css</span>
                                Özel CSS
                            </a>
                            <a href="<?php echo admin_url('design', ['tab' => 'js']); ?>" 
                               class="<?php echo $activeTab === 'js' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">javascript</span>
                                Özel JavaScript
                            </a>
                            <a href="<?php echo admin_url('design', ['tab' => 'head']); ?>" 
                               class="<?php echo $activeTab === 'head' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">code</span>
                                Head Kodu
                            </a>
                        </nav>
                    </div>

                    <?php if ($activeTab === 'files'): ?>
                    <!-- File Editor Layout -->
                    <div class="grid grid-cols-12 gap-6">
                        <!-- File Tree -->
                        <div class="col-span-12 lg:col-span-3">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 sticky top-6">
                                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <span class="material-symbols-outlined text-primary">folder_special</span>
                                        Dosya Yapısı
                                    </h3>
                                </div>
                                <div class="p-2 max-h-[60vh] overflow-y-auto" id="file-tree">
                                    <?php 
                                    if (!function_exists('renderDesignFileTree')) {
                                        function renderDesignFileTree($files, $selectedFile, $path = '') {
                                            foreach ($files as $name => $item) {
                                                if (is_array($item)) {
                                                    // Folder
                                                    $folderPath = $path ? $path . '/' . $name : $name;
                                                    $folderId = 'folder-' . str_replace(['/', '.'], '-', $folderPath);
                                                    ?>
                                                    <div class="mb-1">
                                                        <div class="file-tree-item folder-item flex items-center gap-2 px-3 py-2 rounded text-sm text-gray-700 dark:text-gray-300 cursor-pointer" data-folder="<?php echo $folderId; ?>">
                                                            <span class="material-symbols-outlined text-lg folder-toggle" id="toggle-<?php echo $folderId; ?>">chevron_right</span>
                                                            <span class="material-symbols-outlined text-lg text-yellow-500">folder</span>
                                                            <span><?php echo esc_html($name); ?></span>
                                                        </div>
                                                        <div class="ml-4 folder-content" id="<?php echo $folderId; ?>">
                                                            <?php renderDesignFileTree($item, $selectedFile, $folderPath); ?>
                                                        </div>
                                                    </div>
                                                    <?php
                                                } else {
                                                    // File - $item contains the full relative path
                                                    $filePath = $item;
                                                    $isSelected = $selectedFile === $filePath;
                                                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                                                    $icon = 'description';
                                                    $iconColor = 'text-gray-400';
                                                    if ($ext === 'php') { $icon = 'php'; $iconColor = 'text-indigo-500'; }
                                                    elseif ($ext === 'css') { $icon = 'css'; $iconColor = 'text-blue-500'; }
                                                    elseif ($ext === 'js') { $icon = 'javascript'; $iconColor = 'text-yellow-500'; }
                                                    elseif ($ext === 'json') { $icon = 'data_object'; $iconColor = 'text-green-500'; }
                                                    ?>
                                                    <a href="<?php echo admin_url('design', ['tab' => 'files', 'file' => $filePath]); ?>" 
                                                       class="file-tree-item flex items-center gap-2 px-3 py-2 rounded text-sm <?php echo $isSelected ? 'active text-primary font-medium' : 'text-gray-600 dark:text-gray-400'; ?>">
                                                        <span class="w-5"></span>
                                                        <span class="material-symbols-outlined text-lg <?php echo $iconColor; ?>"><?php echo $icon; ?></span>
                                                        <span><?php echo esc_html($name); ?></span>
                                                    </a>
                                                    <?php
                                                }
                                            }
                                        }
                                    }
                                    
                                    if (!empty($themeFiles)) {
                                        renderDesignFileTree($themeFiles, $selectedFile);
                                    } else {
                                        echo '<p class="p-4 text-gray-500 dark:text-gray-400 text-sm">Dosya bulunamadı.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Code Editor -->
                        <div class="col-span-12 lg:col-span-9">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                                <?php if ($selectedFile): ?>
                                <form method="POST" action="<?php echo admin_url('design', ['tab' => 'files']); ?>" id="file-form">
                                    <input type="hidden" name="action" value="save_file">
                                    <input type="hidden" name="file_path" value="<?php echo esc_attr($selectedFile); ?>">
                                    
                                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                                <span class="material-symbols-outlined text-primary">edit_document</span>
                                                <?php echo esc_html(basename($selectedFile)); ?>
                                            </h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-mono">
                                                themes/<?php echo esc_html($activeTheme['slug']); ?>/<?php echo esc_html($selectedFile); ?>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="formatCode()" class="px-3 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors text-sm flex items-center gap-1">
                                                <span class="material-symbols-outlined text-lg">format_align_left</span>
                                                Formatla
                                            </button>
                                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center gap-2">
                                                <span class="material-symbols-outlined text-lg">save</span>
                                                Kaydet
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="border-t border-gray-200 dark:border-gray-700">
                                        <textarea id="code-editor" name="content"><?php echo esc_html($fileContent); ?></textarea>
                                    </div>
                                </form>
                                <?php else: ?>
                                <div class="p-12 text-center">
                                    <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600 mb-4">code</span>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Dosya Seçin</h3>
                                    <p class="text-gray-500 dark:text-gray-400">Düzenlemek için sol taraftaki dosya ağacından bir dosya seçin.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php elseif ($activeTab === 'css'): ?>
                    <!-- Custom CSS Editor -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <form method="POST" action="<?php echo admin_url('design', ['tab' => 'css']); ?>">
                            <input type="hidden" name="action" value="save_custom_code">
                            <input type="hidden" name="code_type" value="css">
                            
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Özel CSS</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Bu CSS kodu tüm sayfalarda &lt;head&gt; içine eklenecektir.</p>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">save</span>
                                    Kaydet
                                </button>
                            </div>
                            
                            <div>
                                <textarea id="code-editor" name="content"><?php echo esc_html($customCss); ?></textarea>
                            </div>
                        </form>
                    </div>

                    <?php elseif ($activeTab === 'js'): ?>
                    <!-- Custom JS Editor -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <form method="POST" action="<?php echo admin_url('design', ['tab' => 'js']); ?>">
                            <input type="hidden" name="action" value="save_custom_code">
                            <input type="hidden" name="code_type" value="js">
                            
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Özel JavaScript</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Bu JS kodu tüm sayfalarda &lt;/body&gt; öncesine eklenecektir.</p>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">save</span>
                                    Kaydet
                                </button>
                            </div>
                            
                            <div>
                                <textarea id="code-editor" name="content"><?php echo esc_html($customJs); ?></textarea>
                            </div>
                        </form>
                    </div>

                    <?php elseif ($activeTab === 'head'): ?>
                    <!-- Custom Head Code Editor -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <form method="POST" action="<?php echo admin_url('design', ['tab' => 'head']); ?>">
                            <input type="hidden" name="action" value="save_custom_code">
                            <input type="hidden" name="code_type" value="head">
                            
                            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Head Kodu</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Meta taglar, analytics kodları vb. için &lt;head&gt; içine eklenecektir.</p>
                                </div>
                                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">save</span>
                                    Kaydet
                                </button>
                            </div>
                            
                            <div>
                                <textarea id="code-editor" name="content"><?php echo esc_html($customHead); ?></textarea>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Info Box -->
                    <div class="mt-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                        <div class="flex">
                            <span class="material-symbols-outlined text-amber-500 mr-3">warning</span>
                            <div>
                                <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Dikkat</h3>
                                <ul class="mt-2 text-sm text-amber-700 dark:text-amber-300 list-disc list-inside space-y-1">
                                    <li>PHP syntax hataları sitenizi bozabilir</li>
                                    <li>Değişiklikler otomatik yedeklenir (.backup dosyaları)</li>
                                    <li>Büyük değişikliklerden önce manuel yedek almanız önerilir</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <?php else: ?>
                    <!-- No Active Theme -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                        <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600 mb-4">style</span>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aktif Tema Yok</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Tasarım düzenlemesi yapabilmek için önce bir tema aktifleştirmeniz gerekiyor.</p>
                        <a href="<?php echo admin_url('themes'); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            <span class="material-symbols-outlined">palette</span>
                            Temalara Git
                        </a>
                    </div>
                    <?php endif; ?>

                </div>
            </main>
        </div>
    </div>

    <!-- CodeMirror JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/selection/active-line.min.js"></script>

    <script>
        // CodeMirror initialization
        let editor;
        const textarea = document.getElementById('code-editor');
        
        if (textarea) {
            // Detect mode based on file extension or tab
            const activeTab = '<?php echo $activeTab; ?>';
            const selectedFile = '<?php echo $selectedFile; ?>';
            let mode = 'php';
            
            if (activeTab === 'css') {
                mode = 'css';
            } else if (activeTab === 'js') {
                mode = 'javascript';
            } else if (activeTab === 'head') {
                mode = 'htmlmixed';
            } else if (selectedFile) {
                const ext = selectedFile.split('.').pop().toLowerCase();
                if (ext === 'css') mode = 'css';
                else if (ext === 'js') mode = 'javascript';
                else if (ext === 'json') mode = 'application/json';
                else mode = 'php';
            }
            
            editor = CodeMirror.fromTextArea(textarea, {
                mode: mode,
                theme: 'dracula',
                lineNumbers: true,
                lineWrapping: true,
                matchBrackets: true,
                autoCloseBrackets: true,
                styleActiveLine: true,
                indentUnit: 4,
                tabSize: 4,
                indentWithTabs: false,
                extraKeys: {
                    "Ctrl-S": function(cm) {
                        document.querySelector('form').submit();
                    },
                    "Cmd-S": function(cm) {
                        document.querySelector('form').submit();
                    }
                }
            });
            
            // Sync content before submit
            document.querySelector('form')?.addEventListener('submit', function() {
                textarea.value = editor.getValue();
            });
        }
        
        // Folder toggle with event delegation
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handler for all folder items
            document.querySelectorAll('.folder-item').forEach(function(item) {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const folderId = this.getAttribute('data-folder');
                    toggleFolder(folderId);
                });
            });
            
            // Open folders that contain selected file
            const selectedFile = '<?php echo addslashes($selectedFile); ?>';
            if (selectedFile) {
                const parts = selectedFile.split('/');
                let path = '';
                for (let i = 0; i < parts.length - 1; i++) {
                    path = path ? path + '/' + parts[i] : parts[i];
                    const folderId = 'folder-' + path.replace(/[\/\.]/g, '-');
                    toggleFolder(folderId, true); // force open
                }
            }
        });
        
        function toggleFolder(folderId, forceOpen = false) {
            const folder = document.getElementById(folderId);
            const toggle = document.getElementById('toggle-' + folderId);
            
            if (!folder) return;
            
            if (forceOpen || !folder.classList.contains('open')) {
                folder.classList.add('open');
                if (toggle) toggle.classList.add('open');
            } else {
                folder.classList.remove('open');
                if (toggle) toggle.classList.remove('open');
            }
        }
        
        // Format code (basic)
        function formatCode() {
            if (editor) {
                // Just re-indent for now
                const totalLines = editor.lineCount();
                for (let i = 0; i < totalLines; i++) {
                    editor.indentLine(i);
                }
            }
        }
    </script>
</body>
</html>
