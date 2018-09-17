# Dynamic Content Builder


List of all Dynamic Content Builder types is available at Structure > DCB 
Component Type (admin/structure/dcb_component_type)

* Add component to a region - /admin/content/dcb_component/add/{parent_id}
* Add component of a specific component type to a region - /admin/content/dcb_component/add/{dcb_component_type}/{parent_id}
* Delete a component in a region - /admin/content/dcb_component/{component_id}/delete
* List components in a region - /admin/content/dcb_component/region/{parent_id}

List of Dynamic Content Builder components is available at Content > DCB (admin/content/dcb_component)


**Theming DCB Components**

DCB components use the following template naming standard (a component named 
heading with ID 1 is used for the example file names):
* Default - dcb_component.html.twig (provided by DCB module)
* Component view mode - dcb_component__default.html.twig
* Component bundle machine name = dcb_component__heading.html.twig
* Component bundle machine name + view mode - dcb_component__heading__default
.html.twig
* DCB component ID - dcb_component__1.html.twig
* DCB component ID + its view mode - dcb_component__1__default.html.twig

