<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VideoStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $data = [
            'categoria' => $this->user()?->categoria_principal,
            'youtube_publicado' => $this->input('youtube_publicado') ?: 'si',
            'youtube_formato_guardado' => $this->input('youtube_formato_guardado') ?: 'no',
        ];

        if ($this->input('tipo_aparato') === 'Otro') {
            $data['tipo_aparato'] = $this->input('tipo_aparato_personalizado');
        }
        if ($this->input('marca') === 'Otra') {
            $data['marca'] = $this->input('marca_personalizada');
        }
        if ($this->input('kg') === 'personalizado') {
            $data['kg'] = $this->input('kg_personalizado');
        }
        if ($this->input('tipo_aparato') !== 'Lavadora') {
            $data['kg'] = null;
        }

        $this->merge($data);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tipo_aparato' => ['required_unless:categoria,Naturaleza', 'nullable', 'string', 'max:255'],
            'categoria' => ['required', 'in:Naturaleza,Linea blanca,Tecnologia,Cocina,Educacion,Otro'],
            'subcategoria' => ['required', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'especie' => ['nullable', 'string', 'max:255'],
            'tipo_aparato_personalizado' => ['nullable', 'required_if:tipo_aparato,Otro', 'string', 'max:255'],
            'marca' => ['required_unless:categoria,Naturaleza', 'nullable', 'string', 'max:255'],
            'marca_personalizada' => ['nullable', 'required_if:marca,Otra', 'string', 'max:255'],
            'kg' => ['nullable', 'numeric', 'gt:0', 'max:999999.99'],
            'kg_personalizado' => ['nullable', 'numeric', 'gt:0', 'max:999999.99'],
            'modelo_comercial' => ['required_unless:categoria,Naturaleza', 'nullable', 'string', 'max:255'],
            'modelo' => ['required_unless:categoria,Naturaleza', 'nullable', 'string', 'max:255'],
            'tecnologia' => ['required_unless:categoria,Naturaleza', 'nullable', 'string', 'max:255'],
            'tema_error' => ['required_unless:categoria,Naturaleza', 'nullable', 'string', 'max:255'],
            'guardado_original' => ['required', 'in:Si,No,Verificar'],
            'ruta_ubicacion_original' => ['nullable', 'required_if:guardado_original,Si', 'string', 'max:255'],
            'guardado_editado' => ['required', 'in:Si,No,Posible'],
            'ruta_ubicacion_editado' => ['nullable', 'required_if:guardado_editado,Si', 'string', 'max:255'],
            'youtube_publicado' => ['required', 'in:si,no'],
            'fecha_subido' => ['nullable', 'required_if:youtube_publicado,si', 'date'],
            'fecha_publicacion' => ['nullable', 'required_if:youtube_publicado,si', 'date', 'after_or_equal:fecha_subido'],
            'titulo' => ['nullable', 'required_if:youtube_publicado,si', 'string', 'max:255'],
            'mes' => ['nullable', 'string', 'max:255'],
            'red_social' => ['nullable', 'string', 'max:255'],
            'formato' => ['nullable', 'required_if:youtube_publicado,si', 'string', 'max:255'],
            'youtube_link' => ['nullable', 'url', 'max:255', Rule::unique('youtube_json_trackers', 'youtube_link')->where(fn ($query) => $query->where('user_id', $this->user()->id))->ignore($this->route('video'))],
            'youtube_ruta_editado' => ['nullable', 'required_if:youtube_formato_guardado,si', 'string', 'max:255'],
            'youtube_miniatura' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'link_lista' => ['nullable', 'url', 'max:255'],
            'titulo_lista' => ['nullable', 'string', 'max:255'],
            'publicado_comunidad' => ['nullable', 'required_if:youtube_publicado,si', 'in:si,no'],
            'descripcion' => ['required', 'string'],
            'linea_tiempo' => ['nullable', 'string'],
            'youtube_formato_guardado' => ['nullable', 'in:si,no'],
            'social' => ['nullable', 'array'],
            'social.facebook' => ['nullable', 'array'],
            'social.tiktok' => ['nullable', 'array'],
            'social.instagram' => ['nullable', 'array'],
            'social.*.publicado' => ['nullable', 'in:si,no'],
            'social.*.titulo' => ['nullable', 'string', 'max:255'],
            'social.*.tipo' => ['nullable', 'string', 'max:255'],
            'social.*.fecha' => ['nullable', 'date'],
            'social.*.link' => ['nullable', 'url', 'max:255'],
            'social.*.formato' => ['nullable', 'string', 'max:255'],
            'social.*.formato_guardado' => ['nullable', 'in:si,no'],
            'social.facebook.miniatura' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'social.tiktok.miniatura' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'social.instagram.miniatura' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}