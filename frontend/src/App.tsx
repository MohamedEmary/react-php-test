import { Component } from "react";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import CategoryPage from "./pages/Category";
import CartContextProvider from "./context/CartContext";
import MainLayout from "./pages/MainLayout";
import NotFound from "./pages/NotFound";

export default class App extends Component {
  router = createBrowserRouter([
    {
      path: "",
      element: <MainLayout />,
      children: [
        {
          index: true,
          element: <CategoryPage />,
        },
        {
          path: "categories/:category",
          element: <CategoryPage />,
        },
        { path: "*", element: <NotFound /> },
      ],
    },
  ]);

  render(): React.ReactNode {
    return (
      <CartContextProvider>
        <RouterProvider router={this.router} />
      </CartContextProvider>
    );
  }
}
