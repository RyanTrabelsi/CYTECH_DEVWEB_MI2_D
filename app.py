from flask import Flask, request, jsonify
from flask_cors import CORS
from transformers import BertTokenizerFast, BertForQuestionAnswering
import torch

app = Flask(__name__)
CORS(app)

model = BertForQuestionAnswering.from_pretrained("./bert-qa-finetuned")
tokenizer = BertTokenizerFast.from_pretrained("./bert-qa-finetuned")

@app.route("/chat", methods=["POST"])
def answer():
    data = request.get_json()
    question = data["question"]
    context = data["context"]  # ou charger automatiquement à partir de tes réponses stockées

    inputs = tokenizer(question, context, return_tensors="pt")
    with torch.no_grad():
        outputs = model(**inputs)
   
    start = torch.argmax(outputs.start_logits)
    end = torch.argmax(outputs.end_logits) + 1
    answer = tokenizer.convert_tokens_to_string(tokenizer.convert_ids_to_tokens(inputs["input_ids"][0][start:end]))

    return jsonify({"answer": answer})

if __name__ == "__main__":
    app.run(debug=True)