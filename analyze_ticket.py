#!/usr/bin/env python3
"""
Script d'analyse de tickets avec OpenAI GPT-4 Vision
"""
import sys
import json
import os
import base64
import requests

def analyze_ticket(image_path, prompt):
    """Analyse un ticket de caisse avec GPT-4 Vision"""
    
    # Le token est lu depuis la variable d'environnement
    api_key = os.getenv('OPENAI_API_KEY')
    
    if not api_key:
        return {
            'success': False,
            'error': 'Token OpenAI manquant (OPENAI_API_KEY)'
        }
    
    try:
        # Lire et encoder l'image en base64
        with open(image_path, 'rb') as image_file:
            image_data = base64.b64encode(image_file.read()).decode('utf-8')
        
        # URL de l'API OpenAI
        url = "https://api.openai.com/v1/chat/completions"
        
        # Préparer la requête
        headers = {
            'Content-Type': 'application/json',
            'Authorization': f'Bearer {api_key}'
        }
        
        payload = {
            "model": "gpt-4o-mini",  # gpt-4o-mini est moins cher et tout aussi efficace pour l'OCR
            "messages": [
                {
                    "role": "user",
                    "content": [
                        {
                            "type": "text",
                            "text": prompt
                        },
                        {
                            "type": "image_url",
                            "image_url": {
                                "url": f"data:image/jpeg;base64,{image_data}"
                            }
                        }
                    ]
                }
            ],
            "max_tokens": 2000,
            "temperature": 0.1
        }
        
        # Envoyer la requête
        response = requests.post(url, headers=headers, json=payload, timeout=60)
        
        if response.status_code != 200:
            return {
                'success': False,
                'error': f"{response.status_code} {response.text}"
            }
        
        result = response.json()
        
        # Extraire le texte de la réponse
        if 'choices' in result and len(result['choices']) > 0:
            content = result['choices'][0]['message']['content']
            return {
                'success': True,
                'content': content
            }
        else:
            return {
                'success': False,
                'error': f"Réponse inattendue: {json.dumps(result)}"
            }
        
    except requests.exceptions.Timeout:
        return {
            'success': False,
            'error': 'Timeout: la requête a pris trop de temps'
        }
    except requests.exceptions.RequestException as e:
        return {
            'success': False,
            'error': f"Erreur réseau: {str(e)}"
        }
    except Exception as e:
        return {
            'success': False,
            'error': f"Erreur: {str(e)}"
        }

if __name__ == '__main__':
    if len(sys.argv) < 3:
        print(json.dumps({
            'success': False,
            'error': 'Usage: python analyze_ticket.py <image_path> <prompt>'
        }))
        sys.exit(1)
    
    image_path = sys.argv[1]
    prompt = sys.argv[2]
    
    result = analyze_ticket(image_path, prompt)
    print(json.dumps(result))
