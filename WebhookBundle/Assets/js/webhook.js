Mautic.sendHookTest = function() {

    var url = mQuery('#webhook_webhookUrl').val();
    var secret = mQuery('#webhook_secret').val();

    // CUSTOM
    var extra = mQuery('#webhook_extra').val();
    var method = mQuery('#webhook_method').val();
    var headers = mQuery('#webhook_headers').val();
    var authType = mQuery('#webhook_authType').val();
    var login = mQuery('#webhook_login').val();
    var password = mQuery('#webhook_password').val();
    var token = mQuery('#webhook_token').val();
    var actualLoad = mQuery('#webhook_actualLoad').val();
    var fieldsWithValues = mQuery('#webhook_fieldsWithValues').val();
    var testContactId = mQuery('#webhook_testContactId').val();
    var subject = mQuery('#webhook_subject').val();
    // CUSTOM

    var eventTypes = mQuery("#event-types input[type='checkbox']");
    var selectedTypes = [];

    eventTypes.each(function() {
        var item = mQuery(this);
        if (item.is(':checked')) {
            selectedTypes.push(item.val());
        }
    });

    var data = {
        action: 'webhook:sendHookTest',
        url: url,
        secret: secret,

        // CUSTOM
        extra: extra,
        method: method,
        headers: headers,
        authType: authType,
        login: login,
        password: password,
        token: token,
        actualLoad: actualLoad,
        fieldsWithValues: fieldsWithValues,
        testContactId: testContactId,
        subject: subject,
        // CUSTOM

        types: selectedTypes
    };

    var spinner = mQuery('#spinner');

    // show the spinner
    spinner.removeClass('hide');

    mQuery.ajax({
        url: mauticAjaxUrl,
        data: data,
        type: 'POST',
        dataType: "json",
        success: function(response) {
            if (response.success) {
                mQuery('#tester').html(response.html);
            }
        },
        error: function (request, textStatus, errorThrown) {
            Mautic.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function(response) {
            spinner.addClass('hide');
        }
    })
};



// CUSTOM
Mautic.sendHookTestPrem = function() {

    var url = mQuery('#webhook_webhookUrl').val();
    var secret = mQuery('#webhook_secret').val();

    // CUSTOM
    var extra = mQuery('#webhook_extra').val();
    var method = mQuery('#webhook_method').val();
    // var headers = mQuery('#webhook_headers').val();
    var authType = mQuery('#webhook_authType').val();
    var login = mQuery('#webhook_login').val();
    var password = mQuery('#webhook_password').val();
    var token = mQuery('#webhook_token').val();
    var actualLoad = mQuery('#webhook_actualLoad').val();
    // var fieldsWithValues = mQuery('#webhook_fieldsWithValues').val();
    var testContactId = mQuery('#webhook_testContactId').val();
    var subject = mQuery('#webhook_subject').val();
    // var form = mQuery('form[name="webhook"]')
    var form = mQuery('form[name="webhook"]').serializeArray()



    // CUSTOM

    var eventTypes = mQuery("#event-types input[type='checkbox']");
    var selectedTypes = [];

    eventTypes.each(function() {
        var item = mQuery(this);
        if (item.is(':checked')) {
            selectedTypes.push(item.val());
        }
    });

    var data = {
        action: 'webhook:sendHookTestPrem',
        url: url,
        secret: secret,

        // CUSTOM
        extra: extra,
        method: method,
        // headers: headers,
        authType: authType,
        login: login,
        password: password,
        token: token,
        actualLoad: actualLoad,
        // fieldsWithValues: fieldsWithValues,
        testContactId: testContactId,
        subject: subject,
        form: form,
        // CUSTOM

        types: selectedTypes
    };

    var spinner = mQuery('#spinner');

    // show the spinner
    spinner.removeClass('hide');

    mQuery.ajax({
        url: mauticAjaxUrl,
        data: data,
        type: 'POST',
        dataType: "json",
        success: function(response) {
            if (response.success) {
                mQuery('#tester').html(response.html);
            }
        },
        error: function (request, textStatus, errorThrown) {
            Mautic.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function(response) {
            spinner.addClass('hide');
        }
    })
};
// CUSTOM
