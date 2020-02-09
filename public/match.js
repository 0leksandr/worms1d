'use strict';

const players = [];
let matchId = null;

const player = (nr, x, y, health = 100) => {
    const width = 50, height = 50;
    const windowWidth  = window.innerWidth;
    const windowHeight = window.innerHeight;

    const selector = `#player${nr}`;
    const element = document.querySelector(selector);
    element.style.top = `${(windowHeight - height / 2 - y)}px`;
    element.style.left = `${(x - width / 2)}px`;
    updateHealthBar();

    function updateHealthBar() {
        document.querySelector(`${selector} .health`).style.width = `${(70 * health / 100)}px`;
    }
    function damage(x) {
        health -= x;
        if (health <= 0) {
            ajax(`/api/stop?match_id=${matchId}`);
            alert("Game over");
            window.location.reload();
        }
        updateHealthBar();
        ajax(`/api/hit?match_id=${matchId}&player=${nr}`);
    }

    const gun = (() => {
        let angle = 0;
        let gx, gy;
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
                `/api/shot?match_id=${matchId}&time=${time}&sx=${sx}&sy=${sy}&shooter_nr=${nr}`,
                response => {
                    id = JSON.parse(response)["missile_id"];
                }
            );

            shooted(() => id, time, sx, sy);
        }
        function shooted(id, time, sx, sy) {
            const dynamic = 1 / 50;
            const element = document.createElement("div");
            element.className = "missile";
            document.body.appendChild(element);

            let i;
            function remove() {
                clearInterval(i);
                document.body.removeChild(element);
                if (id()) ajax(`/api/remove?missile_id=${id()}`);
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
        return {move, shoot, shooted};
    })();

    const player = {x, y, gun, damage, nr};
    players.push(player);
    return player;
};

function ajax(url, callback) {
    const Http = new XMLHttpRequest();
    Http.open("GET", url);
    Http.send();

    Http.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            if (callback) callback(this.responseText);
        }
    }
}

function getCookieValue(a) {
    const b = document.cookie.match('(^|[^;]+)\\s*' + a + '\\s*=\\s*([^;]+)');
    return b ? b.pop() : '';
}

ajax("/api/start?login=test1", response => {
    response = JSON.parse(response);
    console.log(response); // TODO: remove
    const p1 = response["player1"];
    const p2 = response["player2"];

    const player1 = player(1, p1["x"], p1["y"], p1["health"]);
    const player2 = player(2, p2["x"], p2["y"], p2["health"]);

    matchId = response['match_id'];

    response['missiles'].forEach(missile => {
        players[+ missile['shooter_nr'] - 1].gun.shooted(
            () => missile['id'],
            missile['time'],
            missile['sx'],
            missile['sy'],
        );
    });

    document.onmousemove = evt => {
        player1.gun.move(evt.x, window.innerHeight - evt.y);
        player2.gun.move(evt.x, window.innerHeight - evt.y);
    };
    document.onmousedown = () => {
        player1.gun.shoot();
        player2.gun.shoot();
    };
});
