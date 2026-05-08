jQuery(document).ready(function($) {
    const settings = ivSettings || {};
    const uploadMode = settings.uploadMode || 'both';
    const imageFieldKey = settings.imageFieldKey || '';
    const videoFieldKey = settings.videoFieldKey || '';

    function injectWarnings() {
        // Image uploader warning
        if ((uploadMode === 'images' || uploadMode === 'both') && imageFieldKey) {
            const $uploader = $(`input[name="acf\\[${imageFieldKey}\\]"]`).closest('.acf-basic-uploader');
            if ($uploader.length && $uploader.find('.iv-upload-warning').length === 0) {
                $uploader.find('[data-name="add"]').text('Choose Image');
                $uploader.find('[data-name="add"]').parent().after(
                    `<div class="iv-upload-warning" style="margin:20px 0;padding:15px;background:#fff3cd;border:1px solid #ffeaa7;border-radius:8px;color:#856404;font-weight:bold;text-align:center;font-size:1.1em;line-height:1.6;">
                        Only .jpg, .jpeg, .png, .webp, .gif files allowed<br>
                        Maximum file size: ${settings.imageMaxMB || 1} MB
                    </div>`
                );
            }
        }

        // Video uploader warning
        if ((uploadMode === 'videos' || uploadMode === 'both') && videoFieldKey) {
            const $uploader = $(`input[name="acf\\[${videoFieldKey}\\]"]`).closest('.acf-basic-uploader');
            if ($uploader.length && $uploader.find('.iv-upload-warning').length === 0) {
                $uploader.find('[data-name="add"]').text('Choose Video');
                $uploader.find('[data-name="add"]').parent().after(
                    `<div class="iv-upload-warning" style="margin:20px 0;padding:15px;background:#fff3cd;border:1px solid #ffeaa7;border-radius:8px;color:#856404;font-weight:bold;text-align:center;font-size:1.1em;line-height:1.6;">
                        Only .mp4, .mov, .m4v video files allowed<br>
                        Maximum file size: ${settings.videoMaxMB || 30} MB
                    </div>`
                );
            }
        }
    }

    // Client-side validation
    function validateFile($input, isImage) {
        const file = $input[0].files[0];
        if (!file) return;

        const maxSize = isImage ? settings.maxImageSize : settings.maxVideoSize;
        const allowedTypes = isImage ? settings.imageTypes : settings.videoTypes;
        const maxMB = isImage ? settings.imageMaxMB : settings.videoMaxMB;
        const errorMsg = isImage ? settings.errorImageType : settings.errorVideoType;

        if (file.size > maxSize) {
            alert(`File too large! Maximum size is ${maxMB} MB.`);
            $input.val('');
            return;
        }

        if (!allowedTypes.includes(file.type)) {
            alert(errorMsg);
            $input.val('');
        }
    }

    // Attach validation handlers
    if ((uploadMode === 'images' || uploadMode === 'both') && imageFieldKey) {
        $(document).on('change', `input[name="acf\\[${imageFieldKey}\\]"]`, function() {
            validateFile($(this), true);
        });
    }

    if ((uploadMode === 'videos' || uploadMode === 'both') && videoFieldKey) {
        $(document).on('change', `input[name="acf\\[${videoFieldKey}\\]"]`, function() {
            validateFile($(this), false);
        });
    }

    // Run warning injection multiple times to catch ACF async rendering
    injectWarnings();
    setTimeout(injectWarnings, 800);
    setTimeout(injectWarnings, 2000);
    setTimeout(injectWarnings, 4000);

    // Custom title/description labels
    const $titleLabel = $('label[for="acf-_post_title"]');
    const $contentLabel = $('label[for="acf-_post_content"]');

    if ($titleLabel.length && settings.labelTitle) {
        $titleLabel.contents().filter(function() { return this.nodeType === 3; }).first().replaceWith(settings.labelTitle + ': ');
    }
    if ($contentLabel.length && settings.labelContent) {
        $contentLabel.contents().filter(function() { return this.nodeType === 3; }).first().replaceWith(settings.labelContent + ': ');
    }
});

// Final aggressive run after everything
$(window).on('load', function() {
    setTimeout(injectWarnings, 500);
});