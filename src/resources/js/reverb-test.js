window.Echo.channel('test-channel')
    .listen('.test.event', (e) => {
        console.log('REVERB OK:', e);
        alert(e.message);
    });
