from __future__ import annotations

import base64
from typing import Any

import cv2
import face_recognition
import numpy as np
from flask import Flask, Response, jsonify, request

app = Flask(__name__)


def get_image_base64_from_payload(payload: dict[str, Any]) -> str:
    # Keep backward compatibility with both payload keys.
    image_base64 = payload.get("image_base64")
    if isinstance(image_base64, str) and image_base64.strip() != "":
        return image_base64

    image = payload.get("image")
    if isinstance(image, str) and image.strip() != "":
        return image

    return ""


def decode_base64_image(image_base64: str) -> np.ndarray:
    if not isinstance(image_base64, str) or image_base64.strip() == "":
        raise ValueError("image_base64 atau image wajib diisi.")

    payload = image_base64.strip()

    if "," in payload:
        payload = payload.split(",", 1)[1]

    try:
        image_bytes = base64.b64decode(payload, validate=True)
    except Exception as exc:  # noqa: BLE001
        raise ValueError("image_base64 atau image tidak valid.") from exc

    np_buffer = np.frombuffer(image_bytes, dtype=np.uint8)
    bgr_image = cv2.imdecode(np_buffer, cv2.IMREAD_COLOR)

    if bgr_image is None:
        raise ValueError("Gambar tidak dapat diproses.")

    return cv2.cvtColor(bgr_image, cv2.COLOR_BGR2RGB)


def extract_single_face_encoding(rgb_image: np.ndarray) -> tuple[str, np.ndarray | None]:
    face_locations = face_recognition.face_locations(rgb_image, model="hog")

    if len(face_locations) == 0:
        return "no_face", None

    if len(face_locations) > 1:
        return "multiple_faces", None

    face_encodings = face_recognition.face_encodings(
        rgb_image,
        known_face_locations=face_locations,
        num_jitters=1,
    )

    if not face_encodings:
        return "no_face", None

    return "ok", face_encodings[0]


@app.get("/")
def index() -> Any:
    return jsonify(
        {
            "status": "ok",
            "service": "face-ai",
            "message": "Face AI service aktif.",
            "endpoints": ["/health", "/encode", "/recognize"],
        }
    )


@app.get("/favicon.ico")
def favicon() -> Response:
    return Response(status=204)


@app.get("/health")
def health() -> Any:
    return jsonify({"status": "ok", "service": "face-ai"})


@app.post("/encode")
def encode_face() -> Any:
    payload = request.get_json(silent=True) or {}
    image_base64 = get_image_base64_from_payload(payload)

    try:
        rgb_image = decode_base64_image(image_base64)
    except ValueError as exc:
        return jsonify({"status": "invalid_image", "message": str(exc)}), 422

    status, encoding = extract_single_face_encoding(rgb_image)

    if status != "ok":
        return jsonify({"status": status}), 200

    assert encoding is not None

    return jsonify(
        {
            "status": "ok",
            "encoding": [float(value) for value in encoding.tolist()],
        }
    )


@app.post("/recognize")
def recognize_face() -> Any:
    payload = request.get_json(silent=True) or {}
    image_base64 = get_image_base64_from_payload(payload)
    known_faces = payload.get("known_faces", [])
    tolerance = float(payload.get("tolerance", 0.45))

    if not isinstance(known_faces, list) or len(known_faces) == 0:
        return jsonify({"status": "empty_reference", "message": "known_faces kosong."}), 422

    try:
        rgb_image = decode_base64_image(image_base64)
    except ValueError as exc:
        return jsonify({"status": "invalid_image", "message": str(exc)}), 422

    status, live_encoding = extract_single_face_encoding(rgb_image)

    if status != "ok":
        return jsonify({"status": status}), 200

    assert live_encoding is not None

    known_user_ids: list[int] = []
    known_encodings: list[np.ndarray] = []

    for known_face in known_faces:
        if not isinstance(known_face, dict):
            continue

        user_id = known_face.get("user_id")
        encoding_values = known_face.get("encoding")

        if not isinstance(user_id, int):
            continue

        if not isinstance(encoding_values, list):
            continue

        try:
            encoding_array = np.array(encoding_values, dtype=np.float64)
        except Exception:  # noqa: BLE001
            continue

        if encoding_array.shape != (128,):
            continue

        known_user_ids.append(user_id)
        known_encodings.append(encoding_array)

    if not known_encodings:
        return jsonify({"status": "empty_reference", "message": "Semua data referensi encoding tidak valid."}), 422

    distances = face_recognition.face_distance(np.array(known_encodings), live_encoding)
    best_index = int(np.argmin(distances))
    best_distance = float(distances[best_index])

    if best_distance <= tolerance:
        return jsonify(
            {
                "status": "matched",
                "matched_user_id": known_user_ids[best_index],
                "distance": best_distance,
                "confidence_score": max(0.0, 1.0 - best_distance),
            }
        )

    return jsonify(
        {
            "status": "unknown",
            "distance": best_distance,
            "confidence_score": max(0.0, 1.0 - best_distance),
        }
    )


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001, debug=True)
