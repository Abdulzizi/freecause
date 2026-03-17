jQuery(document).ready(function( $ ){
    var ajaxUrl = pubnewsNoticeOject.ajaxUrl, _wpnonce = pubnewsNoticeOject._wpnonce
    var welcomeOption = pubnewsNoticeOject.welcomeOption, themeReviewOption = pubnewsNoticeOject.themeReviewOption
    var themeReviewTempDismiss = pubnewsNoticeOject.themeReviewTempDismiss, upsellNoticeTempDismiss = pubnewsNoticeOject.upsellNoticeTempDismiss
    var themeReviewTempCount = pubnewsNoticeOject.themeReviewTempCount, upsellTempCount = pubnewsNoticeOject.upsellTempCount
    var themeReviewCountId = pubnewsNoticeOject.themeReviewCountId, upsellTempCountId = pubnewsNoticeOject.upsellTempCountId

    var noticeContainer = $('.pubnews-admin-notice')
    if( noticeContainer.length > 0 ) {
        // dismiss notice
        noticeContainer.on('click', '.alert-dismiss, .action-button.review-never, .action-button.already-reviewed', function(){
            var _this = $(this), notice
            if( _this.parents('.pubnews-admin-notice').hasClass( 'pubnews-welcome-notice' ) ) notice = welcomeOption
            if( _this.parents('.pubnews-admin-notice').hasClass( 'pubnews-theme-review-notice' ) ) notice = themeReviewOption
            $.ajax({
                url: ajaxUrl,
                method: "POST",
                data: {
                    "action": 'pubnews_admin_notice_ajax_call',
                    "_wpnonce": _wpnonce,
                    "dismiss_option": notice
                },
                beforeSend: function(){
                    _this.text( 'Dismissing...' )
                },
                success: function( result ) {
                    var parsedResult = JSON.parse( result )
                    if( parsedResult.status ) _this.parents( '.pubnews-admin-notice' ).fadeOut()
                },
                complete: function() {
                    _this.text( 'Dismissed' )
                }
            })
        })

        // for copy link
        var upsellNotice = $('.pubnews-upsell-notice')
        if( upsellNotice.length > 0 ) {
            $(this).on( 'click', '.action-button.copiable', function( event ) { 
                event.preventDefault()
                var couponCodeElement = $(this).find( '.coupon-code' )
                var couponCodeText = couponCodeElement.text()
                var couponCode = couponCodeElement.data( 'code' )
                navigator.clipboard.writeText( couponCode )
                couponCodeElement.text( 'Copied' ).fadeOut( 700, function(){
                    couponCodeElement.show().text( couponCodeText )
                })
            })
        }

        // may be later button
        noticeContainer.on( 'click', '.may-be-later.action-button', function( event ){
            var _this = $(this), notice, duration = 7, count, countID
            if( _this.parents('.pubnews-admin-notice').hasClass( 'pubnews-theme-review-notice' ) ) {
                notice = themeReviewTempDismiss
                count = themeReviewTempCount
                countID = themeReviewCountId
            }
            if( _this.parents('.pubnews-admin-notice').hasClass( 'pubnews-upsell-notice' ) ) {
                notice = upsellNoticeTempDismiss
                count = upsellTempCount
                countID = upsellTempCountId
            }
            count++
            if( count > 1 && count < 3 ) duration = 4
            if ( count >= 3 ) duration = 3
            $.ajax({
                url: ajaxUrl,
                method: "POST",
                data: {
                    "action": 'pubnews_admin_notice_ajax_call',
                    "_wpnonce": _wpnonce,
                    "dismiss_option": notice,
                    "duration": duration,
                    "count": count,
                    "count_id": countID,
                    "is_temporary": true
                },
                beforeSend: function(){
                    _this.text( 'Dismissing...' )
                },
                success: function( result ) {
                    var parsedResult = JSON.parse( result )
                    if( parsedResult.status ) _this.parents( '.pubnews-admin-notice' ).fadeOut()
                },
                complete: function() {
                    _this.text( 'Dismissed' )
                }
            })
        } )
    }
})