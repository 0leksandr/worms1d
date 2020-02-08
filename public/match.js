'use strict';

const players = [];

const player = (nr, x, y) => {
    const direction = 1;
    const width = 50, height = 50;
    let health = 100;
    const windowWidth  = window.innerWidth;
    const windowHeight = window.innerHeight;

    const selector = `#player${nr}`;
    const element = document.querySelector(selector);
    element.style.top = `${(windowHeight - height / 2 - y)}px`;
    element.style.left = `${(x - width / 2)}px`;
    element.style.transform = `scaleX(${direction})`;

    function damage(x) {
        health -= x;
        if (health <= 0) {
            alert("Game over");
            window.location.reload();
        }
        document.querySelector(`${selector} .health`).style.width = `${(70 * health / 100)}px`;
    }

    const gun = (() => {
        let deg = 0;
        let gx, gy;
        function move(cx, cy) {
            gx = cx;
            gy = cy;
            deg = Math.atan((y - cy) / (cx - x));
            if (cx < x) deg += Math.PI;
            document.querySelector(`${selector} .gun`).style.transform = `rotate(${(deg / Math.PI * 180)}deg)`;
        }
        function shoot() {
            const rand = 0.1;
            const speed = 45 * (1 + Math.random() * rand);
            const dynamic = 1 / 20;

            let sx = speed * Math.cos(deg), sy = speed * Math.sin(deg);
            if (gy > y) sy *= -1;
            const element = document.createElement("div");
            element.className = "missile";
            document.body.appendChild(element);

            let i;
            function remove() {
                clearInterval(i);
                document.body.removeChild(element);
            }

            let t = 0;
            i = window.setInterval(
                () => {
                    const mx = x + t * sx;
                    const my = y + sy ** 2 - (t - sy) ** 2;
                    if (mx < 0 || mx > windowWidth || my < 0) remove();
                    players.forEach(player => {
                        if (player.nr !== nr) {
                            if (Math.sqrt((player.x - mx) ** 2 + (player.y - my) ** 2) < 50) {
                                player.damage(10);
                                remove();
                            }
                        }
                    });
                    element.style.left = `${mx}px`;
                    element.style.top = `${(windowHeight - my)}px`;
                    t += dynamic;
                },
                1
            );
        }
        return {move, shoot};
    })();

    const player = {x, y, gun, damage, nr};
    players.push(player);
    return player;
};

const player1 = player(1, 100, 100);
const player2 = player(2, window.innerWidth - 100, 100);

document.onmousemove = evt => {
    player1.gun.move(evt.x, window.innerHeight - evt.y);
    player2.gun.move(evt.x, window.innerHeight - evt.y);
};
document.onmousedown = () => {
    player1.gun.shoot();
    player2.gun.shoot();
};
