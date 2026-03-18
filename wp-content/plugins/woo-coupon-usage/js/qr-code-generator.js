jQuery(document).ready(function($) {

    function makeCode(container) {
        if (typeof QRCode === 'undefined') {
            console.log("QRCode library not loaded");
            return;
        }

        var id = container.data('id');
        var id2 = container.data('id2');
        var qrColor = $('#wcu-qr-code-color-picker' + id).val();
        var qrContainerId = "showqrcode" + id;
        
        $("#" + qrContainerId).html("");

        var qrcode = new QRCode(qrContainerId, {
            text: "",
            width: 250,
            height: 250,
            colorDark: qrColor,
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        var referralurl = "";
        if (id2 === "p1") {
            referralurl = $.trim($('#p1short').text());
            if (!referralurl) {
                referralurl = $.trim($('#p1').text());
            }
        } else {
            referralurl = $.trim($('#' + id2).text());
        }

        referralurl = decodeURIComponent(encodeURIComponent(referralurl));

        qrcode.makeCode(referralurl);

        // Logo logic
        var logoEnable = container.data('logo-enable');
        var logoUrl = container.data('logo-url');
        var title = container.data('title');

        if (logoEnable && logoUrl) {
            setTimeout(function() {
                var qrImg = $('#' + qrContainerId + ' img')[0];
                if (qrImg && qrImg.src) {
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');
                    canvas.width = 250;
                    canvas.height = 250;

                    var img = new Image();
                    img.onload = function() {
                        ctx.drawImage(img, 0, 0, 250, 250);

                        var logoSize = 50;
                        var clearSize = 50;
                        var x = (canvas.width - clearSize) / 2;
                        var y = (canvas.height - clearSize) / 2;

                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(x, y, clearSize, clearSize);

                        ctx.strokeStyle = '#000000';
                        ctx.lineWidth = 1;
                        ctx.strokeRect(x, y, clearSize, clearSize);

                        var logo = new Image();
                        logo.onload = function() {
                            var logoX = (canvas.width - logoSize) / 2;
                            var logoY = (canvas.height - logoSize) / 2;
                            ctx.drawImage(logo, logoX, logoY, logoSize, logoSize);

                            qrImg.src = canvas.toDataURL('image/png');
                            qrImg.setAttribute('data-logo-added', 'true');
                        };
                        logo.src = logoUrl;
                    };
                    img.src = qrImg.src;
                }
            }, 300);
        }
    }

    // Event delegation for Generate button
    $(document).on('click', '.wcusage_landing_qr_show', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var container = $(".display-qr-code" + id);
        
        if (container.is(":visible")) {
            container.hide();
        } else {
            $("#showqrcode" + id).show();
            container.css("opacity", 0).css("display", "inline-block").animate({ opacity: 1 }, 500);
            $("#wcu-download-qr" + id).show();
            
            setTimeout(function() {
                try {
                    makeCode(container);
                } catch (err) {
                    console.log("Error generating QR code: " + err);
                }
            }, 50);
        }
    });

    // Event delegation for Color Picker
    $(document).on('change', '.wcu-qr-code-color-picker-input', function() {
        var id = $(this).attr('id').replace('wcu-qr-code-color-picker', '');
        var container = $(".display-qr-code" + id);
        setTimeout(function() {
            makeCode(container);
        }, 200);
    });

    // Event delegation for Campaign Change
    $(document).on('change', '#wcu-referral-campaign', function() {
        $('.wcu-display-qr-code:visible').each(function() {
            var container = $(this);
            setTimeout(function() {
                makeCode(container);
            }, 200);
        });
    });

    // Event delegation for Custom URL Input
    $(document).on('input', '.wcusage_custom_ref_url', function() {
        $('.wcu-display-qr-code:visible').each(function() {
            var container = $(this);
            setTimeout(function() {
                makeCode(container);
            }, 200);
        });
    });

    // Event delegation for Short URL Generate
    $(document).on('click', '#generate-short-url', function() {
        $('.wcu-display-qr-code:visible').each(function() {
            var container = $(this);
            setTimeout(function() {
                makeCode(container);
            }, 2500);
        });
    });

    // Download Functionality
    window.wcusage_downloadQR = function(id) {
        var container = $(".display-qr-code" + id);
        var title = container.data('title');
        var logoEnable = container.data('logo-enable');
        var logoUrl = container.data('logo-url');
        
        var img = $('#showqrcode' + id + ' img')[0];
        
        if (img) {
            if (logoEnable && logoUrl) {
                var checkLogo = function() {
                    if (img.getAttribute('data-logo-added') === 'true') {
                        downloadImage(img.src, title);
                    } else {
                        setTimeout(checkLogo, 100);
                    }
                };
                checkLogo();
            } else {
                downloadImage(img.src, title);
            }
        }
    };

    function downloadImage(dataUrl, title) {
        var imgName = 'qr-' + title;
        var a = document.createElement('a');
        a.href = dataUrl;
        a.download = imgName + '.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
    
    // Attach click handler for download buttons
    $(document).on('click', '.wcu-download-qr', function() {
        var id = $(this).attr('id').replace('wcu-download-qr', '');
        wcusage_downloadQR(id);
    });

    // Auto-generate QR codes for shortcode
    $('.wcu-display-qr-code-shortcode').each(function() {
        var container = $(this);
        var id = container.data('id');
        $("#showqrcode" + id).show();
        $("#wcu-download-qr" + id).show();
        setTimeout(function() {
            makeCode(container);
        }, 200);
    });

});
