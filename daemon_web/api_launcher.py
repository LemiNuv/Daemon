from flask import Flask, request, jsonify
import subprocess

app = Flask(__name__)


@app.route("/start", methods=["POST"])
def start_world():
    data = request.json

    template = data.get("template")
    user_id = data.get("user_id")
    world_name = data.get("world_name")

    if not template or not user_id or not world_name:
        return jsonify({"error": "Faltan parámetros"}), 400

    try:
        command = [
            "/home/usuario/luanti/start_server.sh",
            str(template),
            str(user_id),
            str(world_name),
        ]
        output = subprocess.check_output(command, stderr=subprocess.STDOUT).decode()

        if "Servidor iniciado" in output:
            return jsonify({"status": "ok", "message": output}), 200
        else:
            return jsonify({"status": "error", "message": output}), 500

    except subprocess.CalledProcessError as e:
        return jsonify({"status": "error", "message": e.output.decode()}), 500


@app.route("/stop", methods=["POST"])
def stop_world():
    data = request.json

    user_id = data.get("user_id")
    world_name = data.get("world_name")

    if not user_id or not world_name:
        return jsonify({"error": "Faltan parámetros"}), 400

    try:
        command = [
            "/home/usuario/luanti/stop_world.sh",
            str(user_id),
            str(world_name),
        ]
        output = subprocess.check_output(command, stderr=subprocess.STDOUT).decode()

        if "Contenedor detenido" in output:
            return jsonify({"status": "ok", "message": output}), 200
        else:
            return jsonify({"status": "error", "message": output}), 500

    except subprocess.CalledProcessError as e:
        return jsonify({"status": "error", "message": e.output.decode()}), 500

@app.route("/init", methods=["POST"])
def init_world():
    data = request.json

    user_id = data.get("user_id")
    world_name = data.get("world_name")

    if not user_id or not world_name:
        return jsonify({"error": "Faltan parámetros"}), 400

    try:
        command = [
            "/home/usuario/luanti/start_world.sh",
            str(user_id),
            str(world_name),
        ]
        output = subprocess.check_output(command, stderr=subprocess.STDOUT).decode()

        if "Contenedor iniciado" in output:
            return jsonify({"status": "ok", "message": output}), 200
        else:
            return jsonify({"status": "error", "message": output}), 500

    except subprocess.CalledProcessError as e:
        return jsonify({"status": "error", "message": e.output.decode()}), 500


@app.route("/delete", methods=["POST"])
def delete_world():
    data = request.json

    user_id = data.get("user_id")
    world_name = data.get("world_name")

    if not user_id or not world_name:
        return jsonify({"error": "Faltan parámetros"}), 400

    try:
        command = [
            "/home/usuario/luanti/delete_world.sh",
            str(user_id),
            str(world_name),
        ]
        output = subprocess.check_output(command, stderr=subprocess.STDOUT).decode()

        if "Cambio exitoso en base de datos" in output:
            return jsonify({"status": "ok", "message": output}), 200
        else:
            return jsonify({"status": "error", "message": output}), 500

    except subprocess.CalledProcessError as e:
        return jsonify({"status": "error", "message": e.output.decode()}), 500


@app.route("/status", methods=["POST"])
def world_status():
    data = request.json

    user_id = data.get("user_id")
    world_name = data.get("world_name")

    if not user_id or not world_name:
        return jsonify({"error": "Faltan parámetros"}), 400

    try:
        command = [
            "/home/usuario/luanti/check_status.sh",
            str(user_id),
            str(world_name),
        ]
        output = subprocess.check_output(command, stderr=subprocess.STDOUT).decode()

        if "Error" in output:
            return jsonify({"status": "error", "message": output}), 404

        return jsonify({
            "status": "ok",
            "container_status": output,
            "message": f"Estado actual: {output}"
        }), 200

    except subprocess.CalledProcessError as e:
        return jsonify({"status": "error", "message": e.output.decode()}), 500

@app.route("/add_mods", methods=["POST"])
def add_mods():
    data = request.json
    template = data.get("template")
    user_id = data.get("user_id")
    world_name = data.get("world_name")
    mods = data.get("mods", [])

    if not template or not user_id or not world_name:
        return jsonify({"error": "Faltan parámetros"}), 400

    try:
        command = [
            "/home/usuario/luanti/add_mods.sh",
            template,
            str(user_id),
            str(world_name)
        ] + mods

        output = subprocess.check_output(command, stderr=subprocess.STDOUT).decode()

        if "Servidor iniciado" in output:
            return jsonify({"status": "ok", "message": output}), 200
        else:
            return jsonify({"status": "error", "message": output}), 500

    except subprocess.CalledProcessError as e:
        return jsonify({"status": "error", "message": e.output.decode()}), 500


if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000)
