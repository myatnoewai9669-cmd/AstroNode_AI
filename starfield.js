(function() {
    const canvas = document.getElementById('starfield');
    const ctx = canvas.getContext('2d');
    let stars = [];

    function resize() {
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
    }

    function initStars() {
        stars = Array.from({ length: 180 }, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            r: Math.random() * 1.5 + 0.3,
            alpha: Math.random() * 0.7 + 0.1,
            twinkle: Math.random() * Math.PI * 2,
            twinkleSpeed: Math.random() * 0.02 + 0.005,
        }));
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        stars.forEach(s => {
            s.twinkle += s.twinkleSpeed;
            const a = s.alpha * (0.5 + 0.5 * Math.sin(s.twinkle));
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(255,255,255,${a})`;
            ctx.fill();
        });
        requestAnimationFrame(draw);
    }

    resize(); initStars(); draw();
    window.addEventListener('resize', () => { resize(); initStars(); });
})();