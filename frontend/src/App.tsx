import { Component } from "react";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import CartContextProvider from "./context/CartContext";
import MainLayout from "./pages/MainLayout";
import NotFound from "./pages/NotFound";
import CategoryPageWrapper from "./pages/Category";
import ProductPageWrapper from "./pages/Product";
import UserContextProvider from "./context/UserContext";
import { Toaster } from "react-hot-toast";

export default class App extends Component {
  router = createBrowserRouter([
    {
      path: "",
      element: <MainLayout />,
      children: [
        {
          index: true,
          element: <CategoryPageWrapper />,
        },
        {
          path: "categories/:category",
          element: <CategoryPageWrapper />,
        },
        {
          path: "product/:id",
          element: <ProductPageWrapper />,
        },
        { path: "*", element: <NotFound /> },
      ],
    },
  ]);

  render(): React.ReactNode {
    return (
      <UserContextProvider>
        <CartContextProvider>
          <Toaster />
          <RouterProvider router={this.router} />
        </CartContextProvider>
      </UserContextProvider>
    );
  }
}
