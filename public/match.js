'use strict';

const players = [];
let matchId = null;

const player = (nr, login, x, y, health = 100) => {
    const width = 50, height = 50;
    const windowWidth  = window.innerWidth;
    const windowHeight = window.innerHeight;
    if (x < 0) x = windowWidth + x;

    const selector = `#player${nr}`;
    const element = document.querySelector(selector);
    element.style.top = `${(windowHeight - height / 2 - y)}px`;
    element.style.left = `${(x - width / 2)}px`;
    document.querySelector(`${selector} .login`).innerHTML = login;
    updateHealthBar();

    function updateHealthBar() {
        document.querySelector(`${selector} .health`).style.width = `${(70 * health / 100)}px`;
    }
    function damage(x) {
        health -= x;
        if (health <= 0) {
            alert("Game over");
            ajax(`stop?match_id=${matchId}&killed=${nr}`, () => window.location.reload());
        }
        updateHealthBar();
        ajax(`hit?match_id=${matchId}&player=${nr}`);
    }

    const gun = (() => {
        let angle = 0;
        let gx, gy;
        const missileIds = [];
        function move(cx, cy) {
            gx = cx;
            gy = cy;
            angle = Math.atan((y - cy) / (cx - x));
            if (cx < x) angle += Math.PI;
            document.querySelector(`${selector} .gun`).style.transform = `rotate(${(angle / Math.PI * 180)}deg)`;
        }
        function shoot() {
            const rand  = 0.1;
            const speed = 45 * (1 + Math.random() * rand);

            let sx = speed * Math.cos(angle), sy = speed * Math.sin(angle);
            if (gy > y) sy *= -1;

            let id   = null;
            let time = Date.now();
            ajax(
                `shot?match_id=${matchId}&time=${time}&sx=${sx}&sy=${sy}&shooter_nr=${nr}`,
                response => {
                    id = JSON.parse(response)["missile_id"];
                }
            );

            launched(() => id, time, sx, sy);
        }
        function launched(id, time, sx, sy) {
            const dynamic = 1 / 50;
            const element = document.createElement("div");
            element.className = "missile";
            document.body.appendChild(element);

            if (id()) missileIds.push(id());

            let i;
            function remove() {
                clearInterval(i);
                document.body.removeChild(element);
                if (id()) {
                    const index = missileIds.indexOf(id());
                    if (index !== -1) missileIds.splice(index, 1);

                    ajax(`remove?missile_id=${id()}`);
                }
            }

            i = window.setInterval(
                () => {
                    const t = Date.now() - time;
                    const mx = x + t * dynamic * sx;
                    const my = y + sy ** 2 - (t * dynamic - sy) ** 2;
                    if (mx < 0 || mx > windowWidth || my < 0) {
                        remove();
                    }
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
                },
                1
            );
        }
        function has(id) {
            return missileIds.includes(id);
        }
        return {move, shoot, launched, has};
    })();

    const player = {x, y, gun, damage, nr};
    players.push(player);
    return player;
};

function ajax(uri, callback) {
    const Http = new XMLHttpRequest();
    Http.open("GET", "/api/" + uri);
    Http.send();

    Http.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (callback) callback(this.responseText);
        }
    }
}

const i = window.setInterval(
    () => {
        document.querySelector('#message').innerHTML =
            'Waiting for opponent' + Array(Math.round(Date.now() / 1000) % 3 + 1).fill('.').join('');
        ajax("start", response => {
            response = JSON.parse(response);
            if (response['match_id']) {
                window.clearInterval(i);
                matchId = response['match_id'];
            }
        });
        document.querySelector("#bot").onclick = () => {
            window.clearInterval(i);
            ajax("bot", response => {
                matchId = response['match_id'];
                window.location.reload();
            });
        };
    },
    500
);

function launched(missiles) {
    missiles.forEach(missile => {
        const player = players[+missile['shooter_nr'] - 1];
        if (!player.gun.has(missile['id'])) {
            player.gun.launched(
                () => missile['id'],
                missile['time'],
                missile['sx'],
                missile['sy'],
            );
        }
    });
}

window.setInterval(
    () => {
        if (matchId) {
            ajax(`update?match_id=${matchId}`, response => {
                response = JSON.parse(response);

                const p1 = response["player1"];
                const p2 = response["player2"];

                const player1 = player(1, p1["login"], p1["x"], p1["y"], p1["health"]);
                const player2 = player(2, p2["login"], p2["x"], p2["y"], p2["health"]);

                matchId = response['match_id'];
                launched(response['missiles']);

                const _player        = response['login'] === p1['login'] ? player1 : player2;
                document.onmousemove = evt => _player.gun.move(evt.x, window.innerHeight - evt.y);
                document.onmousedown = () => _player.gun.shoot();

                if (p2['login'] === 'bot') {
                    player2.gun.move(Math.random() * 1000, Math.random() * 500);
                    player2.gun.shoot();
                }
            });
        }
    },
    500
);
