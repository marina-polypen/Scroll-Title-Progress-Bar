(function ($) {
    $(document).ready(function () {
        const headings = stpbOptions.headings.split(',');
        const progressBar = $('.scroll-progress-bar');
        const titleBox = $('.scroll-title');
        const progressWrap = $('.scroll-progress');
        let visibleHeading = null;
        let isMobile = window.innerWidth <= stpbOptions.mobile_breakpoint;
        let lastScrollTop = 0;
        let scrollTimeout;
        let dropdown = null;

        // Set CSS variables based on admin settings
        document.documentElement.style.setProperty('--stpb-color', stpbOptions.color);
        document.documentElement.style.setProperty('--stpb-height', stpbOptions.height + 'px');
        document.documentElement.style.setProperty('--stpb-opacity', stpbOptions.opacity);
        document.documentElement.style.setProperty('--stpb-heading-text-size', stpbOptions.heading_text_size + 'px');
        document.documentElement.style.setProperty('--stpb-animation-speed', stpbOptions.animation_speed + 'ms');

        // Create headings dropdown if enabled
        if (stpbOptions.show_headings_dropdown) {
            dropdown = $('<div class="headings-dropdown"></div>');
            titleBox.after(dropdown);

            // Function to update headings list
            function updateHeadingsList() {
                if (!dropdown) return;
                
                dropdown.empty();
                let selector = headings.join(',');
                
                // Exclude sidebar and footer if enabled
                if (stpbOptions.exclude_sidebar_footer) {
                    selector += ':not(.sidebar *, .widget-area *, .footer *, .site-footer *, aside *, .comments-area *, #footer *, .footer-widgets *, .site-footer *, .widget *, .widget-area *, .sidebar-widget *, footer *, footer)';
                }

                const pageHeadings = $(selector);
                
                if (pageHeadings.length === 0) {
                    const noHeadings = $('<div class="heading-item">No headings found</div>');
                    dropdown.append(noHeadings);
                    return;
                }

                pageHeadings.each(function () {
                    const heading = $(this);
                    const text = heading.text().trim();
                    if (text) {
                        const item = $('<div class="heading-item"></div>')
                            .text(text)
                            .data('heading', heading)
                            .addClass(heading.prop('tagName').toLowerCase())
                            .on('click', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                smoothScroll(heading);
                            });
                        dropdown.append(item);
                    }
                });
            }

            // Initial update of headings list
            updateHeadingsList();

            // Update headings list on resize
            $(window).on('resize', function() {
                updateHeadingsList();
            });

            // Show dropdown on title hover
            titleBox.on('mouseenter', function() {
                if (dropdown) {
                    dropdown.show();
                }
            });

            // Hide dropdown when mouse leaves
            progressWrap.on('mouseleave', function() {
                if (dropdown) {
                    dropdown.hide();
                }
            });
        }

        // Handle window resize
        $(window).on('resize', function() {
            isMobile = window.innerWidth <= stpbOptions.mobile_breakpoint;
            if (!stpbOptions.show_on_mobile && isMobile) {
                progressWrap.hide();
            } else {
                progressWrap.show();
            }
        });

        // Initial check for mobile
        if (!stpbOptions.show_on_mobile && isMobile) {
            progressWrap.hide();
        }

        // Smooth scroll function
        function smoothScroll(target) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, stpbOptions.animation_speed);
        }

        // Update progress bar with throttling
        function updateProgress() {
            if (scrollTimeout) return;
            
            scrollTimeout = setTimeout(function() {
                let scrollTop = $(window).scrollTop();
                let docHeight = $(document).height() - $(window).height();
                let progress = (scrollTop / docHeight) * 100;
                
                // Animate progress bar
                progressBar.css('width', progress + '%');

                // Find visible heading
                visibleHeading = null;
                let currentHeading = null;
                let selector = headings.join(',');
                
                // Exclude sidebar and footer if enabled
                if (stpbOptions.exclude_sidebar_footer) {
                    selector += ':not(.sidebar *, .widget-area *, .footer *, .site-footer *, aside *, .comments-area *, #footer *, .footer-widgets *, .site-footer *, .widget *, .widget-area *, .sidebar-widget *, footer *, footer)';
                }

                $(selector).each(function () {
                    const heading = $(this);
                    const headingTop = heading.offset().top;
                    if (headingTop <= scrollTop + 200) {
                        visibleHeading = heading.text().trim();
                        currentHeading = heading;
                    }
                });

                // Update active state in dropdown if enabled
                if (stpbOptions.show_headings_dropdown && dropdown) {
                    dropdown.find('.heading-item').removeClass('active');
                    if (currentHeading) {
                        dropdown.find('.heading-item').each(function() {
                            if ($(this).text() === visibleHeading) {
                                $(this).addClass('active');
                            }
                        });
                    }
                }

                // Update title box with animation
                if (visibleHeading) {
                    if (titleBox.text() !== visibleHeading) {
                        titleBox.fadeOut(stpbOptions.animation_speed / 2, function() {
                            titleBox.text(visibleHeading).fadeIn(stpbOptions.animation_speed / 2);
                        });
                    }
                    progressWrap.addClass('active');
                } else {
                    progressWrap.removeClass('active');
                }

                // Hide/show based on scroll direction
                if (stpbOptions.position === 'top') {
                    if (scrollTop > lastScrollTop && scrollTop > 100) {
                        progressWrap.addClass('hidden');
                    } else {
                        progressWrap.removeClass('hidden');
                    }
                }

                lastScrollTop = scrollTop;
                scrollTimeout = null;
            }, 10); // Throttle to 100fps
        }

        // Scroll event handler
        $(window).on('scroll', updateProgress);

        // Click handler for title box
        titleBox.on('click', function (e) {
            e.preventDefault();
            const firstHeading = $(headings.join(',')).first();
            if (firstHeading.length) {
                smoothScroll(firstHeading);
            }
        });

        // Add keyboard navigation
        $(document).on('keydown', function(e) {
            if (e.ctrlKey && e.key === 'ArrowUp') {
                e.preventDefault();
                const currentHeading = $(headings.join(',')).filter(function() {
                    return $(this).offset().top > $(window).scrollTop();
                }).first();
                if (currentHeading.length) {
                    smoothScroll(currentHeading);
                }
            }
        });
    });
})(jQuery);
