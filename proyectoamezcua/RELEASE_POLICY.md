# Política de Liberación (Release Policy)

## Versionado
- Usamos **semver**: `MAJOR.MINOR.PATCH` (ej. `1.2.0`).
- El número de versión actual vive en `VERSION.txt`.

## Criterios para liberar
- [ ] Todas las pruebas pasan en CI (lint PHP y tests si existen).
- [ ] Revisión y aprobación de **2 revisores** en Pull Request.
- [ ] QA valida funcionalidad mínima (login, productos, usuarios).
- [ ] Documentación actualizada: `README.md` y `CHANGELOG.md`.
- [ ] Se actualizó `VERSION.txt` y se generó tag `vX.Y.Z`.

## Flujo de PR y Merge
1. Crear rama `release/x.y.z` desde `main`.
2. Abrir Pull Request → **2 aprobaciones**.
3. CI debe pasar.
4. QA firma en comentarios de la PR (texto: “QA OK vX.Y.Z”).
5. Merge por **squash** a `main`.

## Tag y Release
- Crear tag **anotado** `vX.Y.Z`.
- Generar **GitHub Release** con notas del `CHANGELOG.md`.
- Adjuntar evidencia (capturas del pipeline y pruebas).

## Responsables
- **Líder técnico**: autoriza el merge final.
- **QA**: valida check-list de pruebas y firma release.
- **Dev**: mantiene CHANGELOG y bump de versión.

